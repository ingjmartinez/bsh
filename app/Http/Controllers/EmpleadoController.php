<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Models\VwUsuariosUnion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmpleadoController extends Controller
{
    private const EMPRESAS_RRHH = [
        '126' => 'Business Support Hub',
        '100' => 'Consorcio SH-QPL',
    ];

    public function index()
    {
        return view('empleado.index');
    }

    public function list(Request $request)
    {
        $empresa = trim((string) $request->query('empresa', ''));

        $query = Empleado::select(
            DB::raw("CASE
                WHEN companyid = '126' THEN 'Business Support Hub'
                WHEN companyid = '100' THEN 'Consorcio SH-QPL'
                ELSE CONCAT('Empresa ', companyid)
            END AS company"),
            'empleadoid',
            'nombres',
            'apellidos',
            DB::raw('fecha_ingreso AS fechaingreso'),
            DB::raw('fecha_egreso AS fechasalida'),
            'cedula',
            DB::raw('NULL AS ciudad'),
            DB::raw('salario AS salariomensual')
        );

        if (array_key_exists($empresa, self::EMPRESAS_RRHH)) {
            $query->where('companyid', $empresa);
        }

        $empleados = $query->get();
        return response()->json($empleados);
    }

    public function dashboard(Request $request)
    {
        $empresa = trim((string) $request->query('empresa', ''));

        $query = Empleado::query();
        if (array_key_exists($empresa, self::EMPRESAS_RRHH)) {
            $query->where('companyid', $empresa);
        }

        $empleados = $query->get([
            'companyid',
            'empleadoid',
            'nombres',
            'apellidos',
            'fecha_ingreso',
            'fecha_egreso',
            'salario',
        ]);

        $normalizados = $empleados->map(function ($empleado) {
            $fechaSalida = trim((string) ($empleado->fecha_egreso ?? ''));
            $salario = is_numeric($empleado->salario) ? (float) $empleado->salario : 0.0;
            $ciudad = 'Sin ciudad';

            return [
                'companyid' => (string) $empleado->companyid,
                'company' => $this->empresaRrhhLabel((string) $empleado->companyid),
                'activo' => $fechaSalida === '',
                'ciudad' => $ciudad,
                'salario' => $salario,
            ];
        });

        $activos = $normalizados->where('activo', true);
        $inactivos = $normalizados->where('activo', false);

        $salarioPorCiudad = $normalizados
            ->groupBy('ciudad')
            ->map(function ($items, $ciudad) {
                $activosCiudad = $items->where('activo', true);

                return [
                    'ciudad' => $ciudad,
                    'salario' => round($activosCiudad->sum('salario'), 2),
                    'empleados' => $items->count(),
                    'activos' => $activosCiudad->count(),
                    'inactivos' => $items->where('activo', false)->count(),
                ];
            })
            ->sortByDesc('salario')
            ->values();

        $salarioPorEmpresa = $activos
            ->groupBy('company')
            ->map(function ($items, $empresaNombre) {
                return [
                    'empresa' => $empresaNombre,
                    'salario' => round($items->sum('salario'), 2),
                    'empleados' => $items->count(),
                ];
            })
            ->values();

        return response()->json([
            'resumen' => [
                'total_empleados' => $normalizados->count(),
                'activos' => $activos->count(),
                'inactivos' => $inactivos->count(),
                'salario_mensual_total' => round($normalizados->sum('salario'), 2),
                'salario_mensual_activos' => round($activos->sum('salario'), 2),
                'salario_mensual_inactivos' => round($inactivos->sum('salario'), 2),
                'salario_promedio' => round($normalizados->avg('salario') ?: 0, 2),
            ],
            'charts' => [
                'estado' => [
                    'labels' => ['Activos', 'Inactivos'],
                    'series' => [$activos->count(), $inactivos->count()],
                ],
                'salario_ciudad' => [
                    'labels' => $salarioPorCiudad->pluck('ciudad')->take(10)->values(),
                    'series' => $salarioPorCiudad->pluck('salario')->take(10)->values(),
                ],
                'empleados_ciudad' => [
                    'labels' => $salarioPorCiudad->pluck('ciudad')->take(10)->values(),
                    'series' => $salarioPorCiudad->pluck('empleados')->take(10)->values(),
                ],
                'salario_empresa' => [
                    'labels' => $salarioPorEmpresa->pluck('empresa')->values(),
                    'series' => $salarioPorEmpresa->pluck('salario')->values(),
                ],
            ],
            'detalle_ciudad' => $salarioPorCiudad->take(12)->values(),
        ]);
    }

    public function sincronizar(Request $request)
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '512M');
        $empresa = trim((string) $request->query('empresa', ''));

        if (!array_key_exists($empresa, self::EMPRESAS_RRHH)) {
            return response()->json(['error' => 'Empresa invalida. Debe ser 126 o 100.'], 422);
        }

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(20)
                ->timeout(180)
                ->withHeaders([
                    'Accept' => 'application/text',
                ])
                ->get('https://apisj.azurewebsites.net/ApiSJ/RRHH/Empleados/Listar', [
                    'strToken' => '78177a3a-3679-4899-bf9f-22d3badeb737',
                    'intIdEmpresa' => $empresa,
                ]);
        } catch (\Throwable $e) {
            Log::error('Error consultando API de empleados', [
                'empresa' => $empresa,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'No se pudo consultar el servicio de empleados.'], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'error' => 'No se pudo obtener la informacion de empleados',
                'status' => $response->status(),
            ], 502);
        }

        $empleados = $response->json();

        if (!is_array($empleados)) {
            Log::error('API de empleados no devolvio un arreglo JSON valido', [
                'empresa' => $empresa,
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'body_preview' => substr($response->body(), 0, 500),
            ]);

            return response()->json(['error' => 'Respuesta invalida del servicio de empleados.'], 502);
        }

        $columnasActualizables = array_values(array_filter((new Empleado())->getFillable(), function ($columna) {
            return !in_array($columna, ['companyid', 'empleadoid'], true);
        }));

        $lote = [];
        $procesados = 0;
        $omitidos = 0;

        try {
            foreach ($empleados as $e) {
                $e = array_change_key_case((array) $e, CASE_UPPER);
                if (empty($e['EMPLEADOID'])) {
                    $omitidos++;
                    continue;
                }

                $lote[] = $this->mapearEmpleadoApi($e, $empresa);
                $procesados++;

                if (count($lote) >= 50) {
                    Empleado::upsert($lote, ['companyid', 'empleadoid'], $columnasActualizables);
                    $lote = [];
                }
            }

            if (!empty($lote)) {
                Empleado::upsert($lote, ['companyid', 'empleadoid'], $columnasActualizables);
            }
        } catch (\Throwable $e) {
            Log::error('Error sincronizando empleados', [
                'empresa' => $empresa,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Error guardando empleados en la base de datos.'], 500);
        }

        if ($procesados === 0) {
            return response()->json([
                'message' => 'No se recibieron registros validos para sincronizar.',
                'total' => count($empleados),
                'procesados' => 0,
                'omitidos' => $omitidos,
            ]);
        }

        return response()->json([
            'message' => 'Datos sincronizados correctamente',
            'total' => count($empleados),
            'procesados' => $procesados,
            'omitidos' => $omitidos,
            'empresa' => $empresa,
            'empresa_nombre' => $this->empresaRrhhLabel($empresa),
        ]);
    }

    private function empresaRrhhLabel(string $empresa): string
    {
        return self::EMPRESAS_RRHH[$empresa] ?? ('Empresa ' . $empresa);
    }

    private function mapearEmpleadoApi(array $e, string $empresa): array
    {
        return [
            'companyid'                => $e['COMPANYID'] ?? $empresa,
            'empleadoid'               => $e['EMPLEADOID'],
            'nombres'                  => $this->limitarTexto(($e['NOMBRES'] ?? null) ?: 'Sin nombre', 100),
            'apellidos'                => $this->limitarTexto(($e['APELLIDOS'] ?? null) ?: 'Sin apellido', 100),
            'cedula'                   => $this->limitarTexto($e['CEDULA'] ?? null, 30),
            'salario'                  => $this->normalizarDecimal($e['SALARIOMENSUAL'] ?? null),
            'fecha_nacimiento'         => $this->normalizarFecha($e['FECHANACIMIENTO'] ?? null),
            'fecha_ingreso'            => $this->normalizarFecha($e['FECHAINGRESO'] ?? null),
            'fecha_egreso'             => $this->normalizarFecha($e['FECHASALIDA'] ?? null),
            'estatus'                  => empty($e['FECHASALIDA']) ? 1 : 0,
            'telefono'                 => $this->limitarTexto($e['TEL1'] ?? ($e['TEL2'] ?? null), 30),
            'email'                    => $this->limitarTexto($e['EMAIL'] ?? null, 150),
            'numero_cuenta'            => $this->limitarTexto($e['CTABANCO'] ?? ($e['CUENTA'] ?? null), 50),
            'tipo_cuenta'              => $this->limitarTexto($e['TIPOCUENTA'] ?? null, 30),
            'fuente_sync'              => 'apisj_rrhh',
            'ultima_sync_at'           => now(),
            'created_at'               => now(),
            'updated_at'               => now(),
        ];
    }

    private function normalizarFecha($valor): ?string
    {
        $texto = trim((string) ($valor ?? ''));

        if ($texto === '' || $texto === '0000-00-00') {
            return null;
        }

        $fecha = substr($texto, 0, 10);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : null;
    }

    private function normalizarDecimal($valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $numero = str_replace(',', '', (string) $valor);

        return is_numeric($numero) ? (float) $numero : null;
    }

    private function limitarTexto($valor, int $limite): ?string
    {
        $texto = trim((string) ($valor ?? ''));

        if ($texto === '') {
            return null;
        }

        return mb_substr($texto, 0, $limite);
    }

    public function store(Request $request)
    {
        $empleado = Empleado::updateOrCreate(
            ['codigo' => $request->codigo],
            [
                'id_empleado' => $request->id_empleado,
                'nombre' => $request->nombre,
                'cedula' => $request->cedula,
                'estado' => $request->estado
            ]
        );

        return response()->json(['success' => true, 'empleado' => $empleado]);
    }

    public function show($id)
    {
        $empleado = Empleado::where('id', $id)->first();
        $agencias = DB::table('coordinador')
            ->where('empleado_id', $empleado->empleadoid)
            ->where('company_id', $empleado->companyid)
            ->pluck('agencia_id')
            ->toArray();
        $empleado->agencias = implode(',', $agencias);
        return response()->json($empleado);
    }

    public function destroy($id)
    {
        Empleado::where('id', $id)->update(['estado' => 0]);
        return response()->json(['success' => true]);
    }

    public function noRegularizados()
    {
        return view('empleado.noregularizados');
    }

    public function listNoRegularizados()
    {
        $empleados = DB::table('empleados_no_regularizados')->get();
        return response()->json($empleados);
    }

    public function ventasSinEmpleado()
    {
        $ventas = VwUsuariosUnion::orderBy('producto_id', 'asc')
            ->paginate(50);

        return view('empleado.ventas-sin-empleado', compact('ventas'));
    }

    public function incentivos()
    {
        $agencias = DB::select("SELECT DISTINCT CAST(terminal AS UNSIGNED) AS agencia_id FROM agencias WHERE aplica_incentivo = 1");
        $agencias = array_map(fn($item) => $item->agencia_id, $agencias);
        $agencias = json_encode($agencias);

        return view('incentivos.empleados', compact('agencias'));
    }

    public function listEmpleados()
    {
        $empleados = Empleado::select(
            'empleadoid',
            'nombres',
            'apellidos',
            'cedula',
            'aplica_incentivo',
            DB::raw("CASE
                WHEN porcentaje_incentivo IS NULL THEN ''
                ELSE CONCAT(FORMAT(porcentaje_incentivo, 2), '%') 
            END AS porcentaje_incentivo"),
            'id',
            DB::raw('NULL AS depto'),
            DB::raw("CASE
                WHEN companyid = 126 THEN 'Business Support Hub'
                WHEN companyid = 100 THEN 'Consorcio SH-QPL'
                ELSE CONCAT('Empresa ', companyid)
            END AS company"),
        )->whereNull('fecha_egreso')->get();
        return response()->json($empleados);
    }

    public function updateEmpleadoIncentivo(Request $request)
    {
        $id = $request->input('id');
        $aplica = $request->input('aplica');
        $porcentaje = $request->input('porcentaje');
        $tipo = $request->input('tipo');
        $agencias = $request->input('agencias', '');

        if ($tipo == 2 || $tipo == 4) {
            if (empty($agencias)) {
                return response()->json(['success' => false, 'message' => 'Debe ingresar al menos una agencia.'], 400);
            }
        }

        Empleado::where('id', $id)->update([
            'aplica_incentivo' => $aplica,
            'porcentaje_incentivo' => $porcentaje,
            'tipo_empleado_incentivo' => $tipo
        ]);

        $empleado = Empleado::where('id', $id)->first();

        DB::table('porcentaje_administrativo')
            ->where('empleado_id', $empleado->empleadoid)
            ->where('company_id', $empleado->companyid)
            ->delete();

        if ($tipo == 3) {
            DB::table('porcentaje_administrativo')->insert([
                'empleado_id' => $empleado->empleadoid,
                'company_id'   => $empleado->companyid,
                'porcentaje'  => $porcentaje,
            ]);
        }

        DB::table('coordinador')
            ->where('empleado_id', $empleado->empleadoid)
            ->where('company_id', $empleado->companyid)
            ->delete();

        if ($tipo == 2 || $tipo == 4) {
            $agencias = explode(',', $agencias);
            foreach ($agencias as $agencia_id) {
                DB::table('coordinador')->insert([
                    'empleado_id' => $empleado->empleadoid,
                    'company_id'   => $empleado->companyid,
                    'agencia_id'  => $agencia_id,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Datos actualizados']);
    }
}
