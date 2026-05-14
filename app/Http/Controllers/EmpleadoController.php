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
    public function index()
    {
        return view('empleado.index');
    }

    public function list(Request $request)
    {
        $empresa = trim((string) $request->query('empresa', ''));

        $query = Empleado::select(
            DB::raw("CASE WHEN companyid = '168'
                THEN 'Grupo Joselito'
                ELSE 'Negosur'
            END AS company"),
            'empleadoid',
            'nombres',
            'apellidos',
            'fechaingreso',
            'fechasalida',
            'cedula',
            'ciudad',
            'salariomensual'
        );

        if (in_array($empresa, ['168', '169'], true)) {
            $query->where('companyid', $empresa);
        }

        $empleados = $query->get();
        return response()->json($empleados);
    }

    public function dashboard(Request $request)
    {
        $empresa = trim((string) $request->query('empresa', ''));

        $query = Empleado::query();
        if (in_array($empresa, ['168', '169'], true)) {
            $query->where('companyid', $empresa);
        }

        $empleados = $query->get([
            'companyid',
            'empleadoid',
            'nombres',
            'apellidos',
            'fechaingreso',
            'fechasalida',
            'ciudad',
            'salariomensual',
        ]);

        $normalizados = $empleados->map(function ($empleado) {
            $fechaSalida = trim((string) ($empleado->fechasalida ?? ''));
            $salario = is_numeric($empleado->salariomensual) ? (float) $empleado->salariomensual : 0.0;
            $ciudad = trim((string) ($empleado->ciudad ?? '')) ?: 'Sin ciudad';

            return [
                'companyid' => (string) $empleado->companyid,
                'company' => (string) $empleado->companyid === '168' ? 'Grupo Joselito' : 'Negosur',
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

        if (!in_array($empresa, ['168', '169'], true)) {
            return response()->json(['error' => 'Empresa invalida. Debe ser 168 o 169.'], 422);
        }

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(20)
                ->timeout(180)
                ->acceptJson()
                ->get('https://apisj.azurewebsites.net/ApiSJ/RRHH/Empleados/Listar', [
                    'strToken' => '87eb2d56-25f3-4d46-9cb0-73c07a550bd2',
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
            return $columna !== 'empleadoid';
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
        ]);
    }

    private function mapearEmpleadoApi(array $e, string $empresa): array
    {
        return [
            'companyid'                => $e['COMPANYID'] ?? $empresa,
            'empleadoid'               => $e['EMPLEADOID'],
            'nombres'                  => $e['NOMBRES'] ?? null,
            'apellidos'                => $e['APELLIDOS'] ?? null,
            'idposicion'               => $e['IDPOSICION'] ?? null,
            'posicion'                 => $e['POSICION'] ?? null,
            'salariomensual'           => $e['SALARIOMENSUAL'] ?? null,
            'iddepto'                  => $e['IDDEPTO'] ?? null,
            'depto'                    => $e['DEPTO'] ?? null,
            'idciudad'                 => $e['IDCIUDAD'] ?? null,
            'ciudad'                   => $e['CIUDAD'] ?? null,
            'idpais'                   => $e['IDPAIS'] ?? null,
            'pais'                     => $e['PAIS'] ?? null,
            'ctabanco'                 => $e['CTABANCO'] ?? null,
            'tipodocidentidad'         => $e['TIPODOCIDENTIDAD'] ?? null,
            'cedula'                   => $e['CEDULA'] ?? null,
            'sexo'                     => $e['SEXO'] ?? null,
            'estadocivil'              => $e['ESTADOCIVIL'] ?? null,
            'nohijos'                  => $e['NOHIJOS'] ?? null,
            'direccion'                => $e['DIRECCION'] ?? null,
            'tel1'                     => $e['TEL1'] ?? null,
            'tel2'                     => $e['TEL2'] ?? null,
            'email'                    => $e['EMAIL'] ?? null,
            'profesion1'               => $e['PROFESION1'] ?? null,
            'profesion2'               => $e['PROFESION2'] ?? null,
            'fechanacimiento'          => $e['FECHANACIMIENTO'] ?? null,
            'fechaingreso'             => $e['FECHAINGRESO'] ?? null,
            'fechasalida'              => $e['FECHASALIDA'] ?? null,
            'iniciovacaciones'         => $e['INICIOVACACIONES'] ?? null,
            'finalvacaciones'          => $e['FINALVACACIONES'] ?? null,
            'clienteid'                => $e['CLIENTEID'] ?? null,
            'codigovendedor'           => $e['CODIGOVENDEDOR'] ?? null,
            'chofer'                   => $e['CHOFER'] ?? null,
            'bombero'                  => $e['BOMBERO'] ?? null,
            'creadopor'                => $e['CREADOPOR'] ?? null,
            'modificadopor'            => $e['MODIFICADOPOR'] ?? null,
            'fechagrabado'             => $e['FECHAGRABADO'] ?? null,
            'fechamodificado'          => $e['FECHAMODIFICADO'] ?? null,
            'atributoprn'              => $e['ATRIBUTOPRN'] ?? null,
            'idsucursalturno'          => $e['IDSUCURSALTURNO'] ?? null,
            'moduloturno'              => $e['MODULOTURNO'] ?? null,
            'idturno'                  => $e['IDTURNO'] ?? null,
            'nocalcularsalario'        => $e['NOCALCULARSALARIO'] ?? null,
            'turnorotativo'            => $e['TURNOROTATIVO'] ?? null,
            'porcientocomision'        => $e['PORCIENTOCOMISION'] ?? null,
            'enporciento'              => $e['ENPORCIENTO'] ?? null,
            'cuenta'                   => $e['CUENTA'] ?? null,
            'cobrador'                 => $e['COBRADOR'] ?? null,
            'mozo'                     => $e['MOZO'] ?? null,
            'clavemozo'                => $e['CLAVEMOZO'] ?? null,
            'lavador'                  => $e['LAVADOR'] ?? null,
            'idsistemaviejo'           => $e['IDSISTEMAVIEJO'] ?? null,
            'viapago'                  => $e['VIAPAGO'] ?? null,
            'idcentrocosto'            => $e['IDCENTROCOSTO'] ?? null,
            'cuentanav'                => $e['CUENTANAV'] ?? null,
            'idbanco'                  => $e['IDBANCO'] ?? null,
            'viapago_banco'            => $e['VIAPAGO_BANCO'] ?? null,
            'idcalendario'             => $e['IDCALENDARIO'] ?? null,
            'preaviso'                 => $e['PREAVISO'] ?? null,
            'cesantia'                 => $e['CESANTIA'] ?? null,
            'vacaciones'               => $e['VACACIONES'] ?? null,
            'navidad'                  => $e['NAVIDAD'] ?? null,
            'viapago_bancoemp'         => $e['VIAPAGO_BANCOEMP'] ?? null,
            'tipocuenta'               => $e['TIPOCUENTA'] ?? null,
            'cuentagastoinfotep'       => $e['CUENTAGASTOINFOTEP'] ?? null,
            'cuentagastoriesgolaboral' => $e['CUENTAGASTORIESGOLABORAL'] ?? null,
            'rutafoto'                 => $e['RUTAFOTO'] ?? null,
            'enperiodo_prepost_natal'  => $e['ENPERIODO_PREPOST_NATAL'] ?? null,
            'en_licencia_medica'       => $e['EN_LICENCIA_MEDICA'] ?? null,
            'tipo_empleado'            => $e['TIPO_EMPLEADO'] ?? null,
            'idplaza'                  => $e['IDPLAZA'] ?? null,
            'doctor'                   => $e['DOCTOR'] ?? null,
        ];
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
            'depto',
            DB::raw("CASE WHEN companyid = 168
                THEN 'Joselito'
                ELSE 'Negosur'
            END AS company"),
        )->where('fechasalida', null)->get();
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
