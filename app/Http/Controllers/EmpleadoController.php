<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Models\VwUsuariosUnion;
use Illuminate\Support\Facades\Cache;
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
        $empresaCache = array_key_exists($empresa, self::EMPRESAS_RRHH) ? $empresa : 'all';
        $cacheKey = 'empleados_dashboard:' . $empresaCache;

        $payload = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($empresa) {
            $aplicarEmpresa = function ($query) use ($empresa) {
                if (array_key_exists($empresa, self::EMPRESAS_RRHH)) {
                    $query->where('companyid', $empresa);
                }

                return $query;
            };

            $condicionActivo = 'fecha_egreso IS NULL';
            $condicionActivoEmpleado = 'e.fecha_egreso IS NULL';

            $resumen = $aplicarEmpresa(DB::table('empleados'))
                ->selectRaw("
                    COUNT(*) AS total_empleados,
                    SUM(CASE WHEN {$condicionActivo} THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN {$condicionActivo} THEN 0 ELSE 1 END) AS inactivos,
                    ROUND(SUM(COALESCE(salario, 0)), 2) AS salario_mensual_total,
                    ROUND(SUM(CASE WHEN {$condicionActivo} THEN COALESCE(salario, 0) ELSE 0 END), 2) AS salario_mensual_activos,
                    ROUND(SUM(CASE WHEN {$condicionActivo} THEN 0 ELSE COALESCE(salario, 0) END), 2) AS salario_mensual_inactivos,
                    ROUND(AVG(COALESCE(salario, 0)), 2) AS salario_promedio
                ")
                ->first();

            $salarioPorCiudad = $aplicarEmpresa(
                DB::table('empleados as e')
                    ->leftJoin('ciudades as c', 'c.id', '=', 'e.ciudad_id')
            )
                ->selectRaw("
                    COALESCE(c.nombre, 'Sin ciudad') AS ciudad,
                    ROUND(SUM(CASE WHEN {$condicionActivoEmpleado} THEN COALESCE(e.salario, 0) ELSE 0 END), 2) AS salario,
                    COUNT(*) AS empleados,
                    SUM(CASE WHEN {$condicionActivoEmpleado} THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN {$condicionActivoEmpleado} THEN 0 ELSE 1 END) AS inactivos
                ")
                ->groupBy(DB::raw("COALESCE(c.nombre, 'Sin ciudad')"))
                ->orderByDesc('salario')
                ->limit(12)
                ->get()
                ->map(function ($fila) {
                    return [
                        'ciudad' => $fila->ciudad,
                        'salario' => (float) $fila->salario,
                        'empleados' => (int) $fila->empleados,
                        'activos' => (int) $fila->activos,
                        'inactivos' => (int) $fila->inactivos,
                    ];
                });

            $salarioPorEmpresa = $aplicarEmpresa(DB::table('empleados'))
                ->whereNull('fecha_egreso')
                ->selectRaw('companyid, ROUND(SUM(COALESCE(salario, 0)), 2) AS salario, COUNT(*) AS empleados')
                ->groupBy('companyid')
                ->get()
                ->map(function ($fila) {
                    return [
                        'empresa' => $this->empresaRrhhLabel((string) $fila->companyid),
                        'salario' => (float) $fila->salario,
                        'empleados' => (int) $fila->empleados,
                    ];
                });

            $activos = (int) ($resumen->activos ?? 0);
            $inactivos = (int) ($resumen->inactivos ?? 0);

            return [
                'resumen' => [
                    'total_empleados' => (int) ($resumen->total_empleados ?? 0),
                    'activos' => $activos,
                    'inactivos' => $inactivos,
                    'salario_mensual_total' => (float) ($resumen->salario_mensual_total ?? 0),
                    'salario_mensual_activos' => (float) ($resumen->salario_mensual_activos ?? 0),
                    'salario_mensual_inactivos' => (float) ($resumen->salario_mensual_inactivos ?? 0),
                    'salario_promedio' => (float) ($resumen->salario_promedio ?? 0),
                ],
                'charts' => [
                    'estado' => [
                        'labels' => ['Activos', 'Inactivos'],
                        'series' => [$activos, $inactivos],
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
                'detalle_ciudad' => $salarioPorCiudad->values(),
            ];
        });

        return response()->json($payload);
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

        Cache::forget('empleados_dashboard:' . $empresa);
        Cache::forget('empleados_dashboard:all');

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
