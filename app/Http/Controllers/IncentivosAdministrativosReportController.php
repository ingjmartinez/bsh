<?php

namespace App\Http\Controllers;

use App\Imports\AgenciasActualizacionMasivaImport;
use App\Models\CoordinadorOperador;
use App\Models\IncentivoAdministrativo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IncentivosAdministrativosReportController extends IncentivosController
{
    private const GRUPOS_ADMINISTRATIVOS_V5 = [
        '1. Gtes. Y Encarg.',
        '2. Monitoreo',
        '4. Operadores',
        '5. Servs. Tecnicos',
        '6. Seguridad',
    ];

    private const GRUPOS_ADMINISTRATIVOS_FIJOS_V5 = [
        '4. Operadores',
        '5. Servs. Tecnicos',
        '6. Seguridad',
    ];

    public function faltantesReporteNuevoIncentivoV4(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cedulas' => 'required|array|min:1',
            'cedulas.*' => 'nullable|string|max:50',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
        ]);

        $cedulas = collect($validated['cedulas'])
            ->map(fn ($cedula) => preg_replace('/\D+/', '', (string) $cedula))
            ->filter()
            ->unique()
            ->values();

        if ($cedulas->isEmpty()) {
            return response()->json([
                'total_monto' => 0,
                'total_faltantes' => 0,
                'data' => [],
            ]);
        }

        $faltantesBet = DB::table('faltantes_bet')
            ->select('identificacion', 'faltante_id', 'monto', 'fecha');

        $faltantesNet = DB::table('faltantes_net')
            ->select('identificacion', 'faltante_id', 'monto', 'fecha');

        $rows = DB::query()
            ->fromSub($faltantesBet->unionAll($faltantesNet), 'faltantes')
            ->whereBetween('fecha', [$validated['fecha_ini'], $validated['fecha_fin']])
            ->whereIn(DB::raw('CAST(identificacion AS UNSIGNED)'), $cedulas->all())
            ->selectRaw('CAST(identificacion AS UNSIGNED) AS cedula')
            ->selectRaw('COUNT(faltante_id) AS cantidad_faltantes')
            ->selectRaw('ROUND(SUM(COALESCE(monto, 0)), 2) AS monto')
            ->groupByRaw('CAST(identificacion AS UNSIGNED)')
            ->orderByDesc('monto')
            ->get();

        $nombresPorCedula = DB::table('empleados')
            ->whereIn(DB::raw('CAST(cedula AS UNSIGNED)'), $rows->pluck('cedula')->all())
            ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
            ->selectRaw("MAX(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')))) AS nombre")
            ->groupByRaw('CAST(cedula AS UNSIGNED)')
            ->pluck('nombre', 'cedula');

        return response()->json([
            'total_monto' => round((float) $rows->sum('monto'), 2),
            'total_faltantes' => (int) $rows->sum('cantidad_faltantes'),
            'data' => $rows->map(function ($row) use ($nombresPorCedula) {
                $nombre = trim((string) ($nombresPorCedula[$row->cedula] ?? ''));

                return [
                    'cedula' => (string) $row->cedula,
                    'nombre' => $nombre !== '' ? $nombre : 'Actualizar en maestro de empleados',
                    'cantidad_faltantes' => (int) $row->cantidad_faltantes,
                    'monto' => round((float) $row->monto, 2),
                ];
            })->values(),
        ]);
    }

    public function recargasPaqueticosReporteNuevoIncentivoV4(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cedulas' => 'required|array|min:1',
            'cedulas.*' => 'nullable|string|max:50',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            'sistema' => 'nullable|string|in:Todos,Lotobet,Lotonet',
        ]);

        $cedulas = collect($validated['cedulas'])
            ->map(fn ($cedula) => preg_replace('/\D+/', '', (string) $cedula))
            ->filter()
            ->unique()
            ->values();

        if ($cedulas->isEmpty()) {
            return response()->json([
                'total_ventas' => 0,
                'total_recargas' => 0,
                'total_paqueticos' => 0,
                'cantidad_ventas' => 0,
                'data' => [],
            ]);
        }

        $buildVentasQuery = function (string $tabla, string $sistema) use ($validated) {
            return DB::table($tabla)
                ->selectRaw(
                    'cedula, monto, fecha, producto_id, tipo, ? AS sistema',
                    [$sistema]
                )
                ->whereBetween('fecha', [$validated['fecha_ini'], $validated['fecha_fin']])
                ->where(function ($query) {
                    $query->whereIn(DB::raw('CAST(producto_id AS SIGNED)'), [-1, -2])
                        ->orWhereIn(DB::raw('LOWER(TRIM(tipo))'), ['recarga', 'recargas', 'paquetico', 'paqueticos']);
                });
        };

        $sistema = $validated['sistema'] ?? 'Todos';

        if ($sistema === 'Lotobet') {
            $ventasQuery = $buildVentasQuery('vt_usuarios_bet', 'Lotobet');
        } elseif ($sistema === 'Lotonet') {
            $ventasQuery = $buildVentasQuery('vt_usuarios_net', 'Lotonet');
        } else {
            $ventasQuery = $buildVentasQuery('vt_usuarios_bet', 'Lotobet')
                ->unionAll($buildVentasQuery('vt_usuarios_net', 'Lotonet'));
        }

        $rows = DB::query()
            ->fromSub($ventasQuery, 'ventas')
            ->whereNotNull('cedula')
            ->where('cedula', '<>', '')
            ->whereIn(DB::raw('CAST(cedula AS UNSIGNED)'), $cedulas->all())
            ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
            ->selectRaw('COUNT(*) AS cantidad_ventas')
            ->selectRaw("ROUND(SUM(CASE WHEN CAST(producto_id AS SIGNED) = -1 OR LOWER(TRIM(tipo)) IN ('recarga', 'recargas') THEN COALESCE(monto, 0) ELSE 0 END), 2) AS total_recargas")
            ->selectRaw("ROUND(SUM(CASE WHEN CAST(producto_id AS SIGNED) = -2 OR LOWER(TRIM(tipo)) IN ('paquetico', 'paqueticos') THEN COALESCE(monto, 0) ELSE 0 END), 2) AS total_paqueticos")
            ->selectRaw('ROUND(SUM(COALESCE(monto, 0)), 2) AS total_ventas')
            ->groupByRaw('CAST(cedula AS UNSIGNED)')
            ->orderByDesc('total_ventas')
            ->get();

        $nombresPorCedula = DB::table('empleados')
            ->whereIn(DB::raw('CAST(cedula AS UNSIGNED)'), $rows->pluck('cedula')->all())
            ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
            ->selectRaw("MAX(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')))) AS nombre")
            ->groupByRaw('CAST(cedula AS UNSIGNED)')
            ->pluck('nombre', 'cedula');

        return response()->json([
            'total_ventas' => round((float) $rows->sum('total_ventas'), 2),
            'total_recargas' => round((float) $rows->sum('total_recargas'), 2),
            'total_paqueticos' => round((float) $rows->sum('total_paqueticos'), 2),
            'cantidad_ventas' => (int) $rows->sum('cantidad_ventas'),
            'data' => $rows->map(function ($row) use ($nombresPorCedula) {
                $nombre = trim((string) ($nombresPorCedula[$row->cedula] ?? ''));

                return [
                    'cedula' => (string) $row->cedula,
                    'nombre' => $nombre !== '' ? $nombre : 'Actualizar en maestro de empleados',
                    'cantidad_ventas' => (int) $row->cantidad_ventas,
                    'total_recargas' => round((float) $row->total_recargas, 2),
                    'total_paqueticos' => round((float) $row->total_paqueticos, 2),
                    'total_ventas' => round((float) $row->total_ventas, 2),
                ];
            })->values(),
        ]);
    }

    public function reporteNuevoIncentivoV5View(): View
    {
        $coordinadores = collect();
        $cedulaLookupKey = function ($cedula) {
            $digits = preg_replace('/\D+/', '', (string) $cedula);
            $normalized = ltrim($digits, '0');

            return $normalized === '' ? '0' : $normalized;
        };
        $empresaCompanyId = function ($empresa) {
            $text = mb_strtolower(trim((string) $empresa));
            if ($text === '') {
                return '';
            }

            if (str_contains($text, 'joselito') || str_contains($text, 'cjoselito') || str_contains($text, 'consorcio')) {
                return '168';
            }

            if (str_contains($text, 'negosur')) {
                return '169';
            }

            return '';
        };
        $empleadoLookupKey = function ($cedula, $empresa = '') use ($cedulaLookupKey, $empresaCompanyId) {
            $companyId = preg_replace('/\D+/', '', (string) $empresa);
            if ($companyId === '') {
                $companyId = $empresaCompanyId($empresa);
            }

            return $cedulaLookupKey($cedula).'|'.$companyId;
        };

        if (
            Schema::hasTable('coordinador_operador')
            && Schema::hasTable('coordinador_operador_agencia')
            && Schema::hasTable('agencias')
        ) {
            $coordinadoresBase = CoordinadorOperador::query()
                ->where('puesto', 'coordinador')
                ->withCount('agencias')
                ->orderBy('nombre')
                ->orderBy('apellido')
                ->get(['id', 'nombre', 'apellido', 'cedula']);

            $empresaPorCoordinador = DB::table('coordinador_operador_agencia as coa')
                ->join('agencias as a', 'a.id', '=', 'coa.agencia_id')
                ->whereIn('coa.coordinador_operador_id', $coordinadoresBase->pluck('id')->all())
                ->whereNotNull('a.empresa')
                ->selectRaw("coa.coordinador_operador_id, TRIM(COALESCE(a.empresa, '')) AS empresa, COUNT(*) AS total")
                ->groupBy('coa.coordinador_operador_id', 'a.empresa')
                ->orderByDesc('total')
                ->get()
                ->groupBy('coordinador_operador_id')
                ->map(fn ($rows) => (string) ($rows->first()->empresa ?? ''));

            $empleadosPorCedula = collect();
            if (
                Schema::hasTable('empleados')
                && Schema::hasColumn('empleados', 'cedula')
                && Schema::hasColumn('empleados', 'empleadoid')
            ) {
                $cedulasCoordinadores = $coordinadoresBase
                    ->pluck('cedula')
                    ->map(fn ($cedula) => preg_replace('/\D+/', '', (string) $cedula))
                    ->filter()
                    ->unique()
                    ->values();

                if ($cedulasCoordinadores->isNotEmpty()) {
                    $empleadosPorCedula = DB::table('empleados')
                        ->whereIn(DB::raw('CAST(cedula AS UNSIGNED)'), $cedulasCoordinadores->all())
                        ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
                        ->selectRaw('TRIM(CAST(companyid AS CHAR)) AS companyid')
                        ->selectRaw('MAX(empleadoid) AS empleadoid')
                        ->selectRaw("MAX(COALESCE(viapago, '')) AS viapago")
                        ->selectRaw("MAX(COALESCE(ciudad, '')) AS ciudad")
                        ->groupByRaw('CAST(cedula AS UNSIGNED), TRIM(CAST(companyid AS CHAR))')
                        ->get()
                        ->mapWithKeys(function ($row) use ($empleadoLookupKey) {
                            return [$empleadoLookupKey($row->cedula, $row->companyid) => $row];
                        });
                }
            }

            $coordinadores = $coordinadoresBase
                ->map(function ($coordinador) use ($empleadosPorCedula, $empleadoLookupKey, $empresaPorCoordinador) {
                    $cedula = (string) ($coordinador->cedula ?? '');
                    $empresa = (string) ($empresaPorCoordinador[$coordinador->id] ?? '');
                    $empleado = $empleadosPorCedula->get($empleadoLookupKey($cedula, $empresa));

                    return [
                        'id' => $coordinador->id,
                        'nombre' => trim(($coordinador->nombre ?? '').' '.($coordinador->apellido ?? '')),
                        'cedula' => $cedula,
                        'empleadoid' => (string) ($empleado->empleadoid ?? ''),
                        'viapago' => (string) ($empleado->viapago ?? ''),
                        'ciudad' => (string) ($empleado->ciudad ?? ''),
                        'empresa' => $empresa,
                        'agencias' => (int) $coordinador->agencias_count,
                        'agencias_validas' => 0,
                        'monto_usuarios' => 0,
                        'pct' => 0.0055,
                    ];
                })
                ->values();
        }

        $administrativosConfig = collect();

        if (
            Schema::hasTable('incentivo_administrativos')
            && Schema::hasColumn('incentivo_administrativos', 'grupo')
            && Schema::hasColumn('incentivo_administrativos', 'nombre')
            && Schema::hasColumn('incentivo_administrativos', 'empresa')
            && Schema::hasColumn('incentivo_administrativos', 'pct_total')
        ) {
            $administrativosColumns = ['grupo', 'nombre', 'empresa', 'pct_total'];
            if (Schema::hasColumn('incentivo_administrativos', 'cedula')) {
                $administrativosColumns[] = 'cedula';
            }

            $administrativosBase = IncentivoAdministrativo::query()
                ->orderBy('grupo')
                ->orderBy('empresa')
                ->orderBy('nombre')
                ->get($administrativosColumns);

            $empleadosAdministrativosPorCedula = collect();
            $puedeValidarAdministrativosActivos = false;
            if (
                Schema::hasColumn('incentivo_administrativos', 'cedula')
                && Schema::hasTable('empleados')
                && Schema::hasColumn('empleados', 'cedula')
                && Schema::hasColumn('empleados', 'empleadoid')
            ) {
                $puedeValidarAdministrativosActivos = true;
                $hasActivo = Schema::hasColumn('empleados', 'activo');
                $hasFechaSalida = Schema::hasColumn('empleados', 'fechasalida');
                $hasFechaSalidaAlt = Schema::hasColumn('empleados', 'fecha_salida');
                $activoCondition = $hasActivo ? 'COALESCE(activo, 1) = 1' : '1 = 1';
                $fechaSalidaChecks = [];

                if ($hasFechaSalida) {
                    $fechaSalidaChecks[] = "(NULLIF(TRIM(CAST(fechasalida AS CHAR)), '') IS NULL OR TRIM(CAST(fechasalida AS CHAR)) = '0000-00-00')";
                }

                if ($hasFechaSalidaAlt) {
                    $fechaSalidaChecks[] = "(NULLIF(TRIM(CAST(fecha_salida AS CHAR)), '') IS NULL OR TRIM(CAST(fecha_salida AS CHAR)) = '0000-00-00')";
                }

                $fechaSalidaCondition = empty($fechaSalidaChecks)
                    ? '1 = 1'
                    : '('.implode(' AND ', $fechaSalidaChecks).')';
                $cedulasAdministrativos = $administrativosBase
                    ->pluck('cedula')
                    ->map(fn ($cedula) => preg_replace('/\D+/', '', (string) $cedula))
                    ->filter()
                    ->unique()
                    ->values();

                if ($cedulasAdministrativos->isNotEmpty()) {
                    $empleadosAdministrativosPorCedula = DB::table('empleados')
                        ->whereIn(DB::raw('CAST(cedula AS UNSIGNED)'), $cedulasAdministrativos->all())
                        ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
                        ->selectRaw('TRIM(CAST(companyid AS CHAR)) AS companyid')
                        ->selectRaw('MAX(empleadoid) AS empleadoid')
                        ->selectRaw("MAX(COALESCE(viapago, '')) AS viapago")
                        ->selectRaw("MAX(COALESCE(ciudad, '')) AS ciudad")
                        ->selectRaw("SUM(CASE WHEN {$activoCondition} AND {$fechaSalidaCondition} THEN 1 ELSE 0 END) AS empleados_activos")
                        ->groupByRaw('CAST(cedula AS UNSIGNED), TRIM(CAST(companyid AS CHAR))')
                        ->get()
                        ->mapWithKeys(function ($row) use ($empleadoLookupKey) {
                            return [$empleadoLookupKey($row->cedula, $row->companyid) => $row];
                        });
                }
            }

            $administrativosBase = $puedeValidarAdministrativosActivos
                ? $administrativosBase
                    ->filter(function ($row) use ($empleadosAdministrativosPorCedula, $empleadoLookupKey) {
                        $cedula = (string) ($row->cedula ?? '');
                        $empleado = $empleadosAdministrativosPorCedula->get($empleadoLookupKey($cedula, $row->empresa ?? ''));

                        return (int) ($empleado->empleados_activos ?? 0) > 0;
                    })
                    ->values()
                : collect();

            $administrativosConfig = $administrativosBase
                ->map(function ($row) use ($empleadosAdministrativosPorCedula, $empleadoLookupKey) {
                    $cedula = (string) ($row->cedula ?? '');
                    $empleado = $empleadosAdministrativosPorCedula->get($empleadoLookupKey($cedula, $row->empresa ?? ''));

                    return [
                        'grupo' => (string) ($row->grupo ?? ''),
                        'nombre' => (string) ($row->nombre ?? ''),
                        'cedula' => $cedula,
                        'empleadoid' => (string) ($empleado->empleadoid ?? ''),
                        'viapago' => (string) ($empleado->viapago ?? ''),
                        'ciudad' => (string) ($empleado->ciudad ?? ''),
                        'empresa' => (string) ($row->empresa ?? ''),
                        'pct' => (float) ($row->pct_total ?? 0),
                    ];
                })
                ->values();
        }

        $terminalesExcluidasIncentivo = $this->terminalesExcluidasIncentivoGuardadas();

        return view('incentivos.incentivos-administrativos', compact('coordinadores', 'administrativosConfig', 'terminalesExcluidasIncentivo'));
    }

    public function sincronizarAdministrativosReporteNuevoIncentivoV5(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rows' => 'required|array',
            'rows.*.id' => 'nullable|integer|exists:incentivo_administrativos,id',
            'rows.*.grupo' => [
                'required',
                'string',
                'max:70',
                Rule::in(self::GRUPOS_ADMINISTRATIVOS_V5),
            ],
            'rows.*.nombre' => 'required|string|max:120',
            'rows.*.empresa' => ['required', 'string', 'max:50', Rule::in(['Consorcio Joselito', 'Negosur'])],
            'rows.*.pct_total' => 'required|numeric|min:0|max:9999999',
        ]);

        $rows = collect($validated['rows'])
            ->map(function ($row) {
                $grupo = trim((string) $row['grupo']);
                $pctTotal = round((float) $row['pct_total'], 4);

                return [
                    'id' => isset($row['id']) ? (int) $row['id'] : null,
                    'grupo' => $grupo,
                    'nombre' => trim((string) $row['nombre']),
                    'empresa' => trim((string) $row['empresa']),
                    'pct_total' => $pctTotal,
                ];
            })
            ->values();

        $porcentajesInvalidos = $rows
            ->filter(fn ($row) => ! in_array($row['grupo'], self::GRUPOS_ADMINISTRATIVOS_FIJOS_V5, true))
            ->filter(fn ($row) => (float) $row['pct_total'] > 100)
            ->values();

        if ($porcentajesInvalidos->isNotEmpty()) {
            return response()->json([
                'message' => 'El % Total no puede ser mayor que 100 para gerentes, encargados o monitoreo.',
            ], 422);
        }

        $duplicados = $rows
            ->groupBy(fn ($row) => strtolower($row['grupo'].'|'.$row['nombre'].'|'.$row['empresa']))
            ->filter(fn ($items) => $items->count() > 1)
            ->keys()
            ->values();

        if ($duplicados->isNotEmpty()) {
            return response()->json([
                'message' => 'Hay filas duplicadas por grupo, nombre y empresa. Ajusta la plantilla antes de guardar.',
            ], 422);
        }

        DB::transaction(function () use ($rows) {
            $idsPayload = $rows
                ->pluck('id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            IncentivoAdministrativo::query()
                ->when(! empty($idsPayload), fn ($query) => $query->whereNotIn('id', $idsPayload))
                ->delete();

            foreach ($rows as $row) {
                $payload = [
                    'grupo' => $row['grupo'],
                    'nombre' => $row['nombre'],
                    'empresa' => $row['empresa'],
                    'pct_total' => $row['pct_total'],
                ];

                if ($row['id']) {
                    IncentivoAdministrativo::whereKey($row['id'])->update($payload);
                } else {
                    IncentivoAdministrativo::create($payload);
                }
            }
        });

        $data = IncentivoAdministrativo::query()
            ->orderBy('grupo')
            ->orderBy('empresa')
            ->orderBy('nombre')
            ->get(['id', 'grupo', 'nombre', 'empresa', 'pct_total'])
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'grupo' => (string) ($row->grupo ?? ''),
                    'nombre' => (string) ($row->nombre ?? ''),
                    'empresa' => (string) ($row->empresa ?? ''),
                    'pct' => (float) ($row->pct_total ?? 0),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Plantilla administrativa guardada correctamente.',
            'data' => $data,
        ]);
    }

    public function reconocerTerminalesExcluidasReporteNuevoIncentivoV5(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'nullable|mimes:xlsx,xls,csv|max:4096',
            'terminales_manual' => 'nullable|string',
        ]);

        if (! $request->hasFile('file') && trim((string) $request->input('terminales_manual', '')) === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Debe seleccionar un archivo o escribir al menos una terminal.',
            ], 422);
        }

        try {
            $terminales = collect();
            $totalFilas = 0;

            if ($request->hasFile('file')) {
                $import = new AgenciasActualizacionMasivaImport;
                Excel::import($import, $request->file('file'));

                $rows = $import->rows ?? collect();
                $totalFilas = $rows->count();
                $terminales = $terminales->merge($rows
                    ->map(function ($rowCollection) {
                        $row = collect($rowCollection)->toArray();

                        return $this->valorColumnaReporteNuevoIncentivoV5($row, ['terminal']);
                    })
                    ->filter(fn ($terminal) => trim((string) $terminal) !== '')
                    ->values());
            }

            $terminales = $terminales
                ->merge($this->extraerTerminalesTextoReporteNuevoIncentivoV5($request->input('terminales_manual', '')))
                ->map(fn ($terminal) => trim((string) $terminal))
                ->filter(fn ($terminal) => $terminal !== '')
                ->unique()
                ->values();

            if ($terminales->isEmpty()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se encontraron terminales para reconocer. La plantilla debe tener una columna llamada Terminal.',
                ], 422);
            }

            $terminalesEncontradas = DB::table('agencias')
                ->whereIn(DB::raw('TRIM(CAST(terminal AS CHAR))'), $terminales->all())
                ->selectRaw('TRIM(CAST(terminal AS CHAR)) AS terminal')
                ->pluck('terminal')
                ->map(fn ($terminal) => trim((string) $terminal))
                ->filter(fn ($terminal) => $terminal !== '')
                ->unique()
                ->values();

            $terminalesNoEncontradas = $terminales
                ->diff($terminalesEncontradas)
                ->values();

            return response()->json([
                'ok' => true,
                'total_filas' => $totalFilas,
                'terminales_leidas' => $terminales->count(),
                'terminales_unicas' => $terminales->count(),
                'encontradas' => $terminalesEncontradas->count(),
                'no_encontradas' => $terminalesNoEncontradas->count(),
                'terminales_encontradas' => $terminalesEncontradas->all(),
                'terminales_no_encontradas' => $terminalesNoEncontradas->all(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error al reconocer terminales: '.$e->getMessage(),
            ], 500);
        }
    }

    public function listarTerminalesExcluidasIncentivoReporteNuevoIncentivoV5(): JsonResponse
    {
        $terminales = $this->terminalesExcluidasIncentivoGuardadas();

        return response()->json([
            'ok' => true,
            'terminales' => $terminales->all(),
            'count' => $terminales->count(),
        ]);
    }

    public function guardarTerminalesExcluidasIncentivoReporteNuevoIncentivoV5(Request $request): JsonResponse
    {
        $request->validate([
            'terminales' => 'nullable',
        ]);

        $terminales = $this->normalizarTerminalesExcluidasReporteNuevoIncentivoV5($request->input('terminales'));

        if (! Schema::hasTable('terminales_excluidas_incentivo')) {
            return response()->json([
                'ok' => false,
                'message' => 'La tabla terminales_excluidas_incentivo no existe. Ejecuta las migraciones pendientes.',
            ], 500);
        }

        $userId = auth()->id();

        DB::transaction(function () use ($terminales, $userId) {
            DB::table('terminales_excluidas_incentivo')
                ->when($terminales->isNotEmpty(), fn ($query) => $query->whereNotIn('terminal', $terminales->all()))
                ->delete();

            foreach ($terminales as $terminal) {
                DB::table('terminales_excluidas_incentivo')->updateOrInsert(
                    ['terminal' => $terminal],
                    [
                        'updated_by' => $userId,
                        'updated_at' => now(),
                        'created_by' => $userId,
                        'created_at' => now(),
                    ]
                );
            }
        });

        $guardadas = $this->terminalesExcluidasIncentivoGuardadas();

        return response()->json([
            'ok' => true,
            'message' => 'Terminales excluidas guardadas correctamente.',
            'terminales' => $guardadas->all(),
            'count' => $guardadas->count(),
        ]);
    }

    public function plantillaTerminalesExcluidasReporteNuevoIncentivoV5(): BinaryFileResponse
    {
        $data = [
            ['Terminal'],
        ];

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
        {
            protected array $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'plantilla_terminales_excluidas_incentivo_v5.xlsx');
    }

    public function reporteNuevoIncentivoV5(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            'sistema' => 'nullable|in:Todos,Lotobet,Lotonet',
            'min_dias_venta' => 'nullable|integer|min:1',
            'filtro_cumplimiento' => 'nullable|in:todos,cumplidos,no_cumplidos',
            'tipo_pago' => 'nullable|string',
            'rangos_pago' => 'nullable|string',
            'modo_calculo' => 'nullable|in:general,separado_empresa',
            'alcance_productos' => 'nullable|in:completo,no_tradicionales',
            'terminales_excluidas' => 'nullable|string',
        ]);

        $modoCalculo = $request->input('modo_calculo', 'general');
        $fechaIniSeleccionada = Carbon::parse($request->input('fecha_ini'))->toDateString();
        $fechaFinSeleccionada = Carbon::parse($request->input('fecha_fin'))->toDateString();
        $sistema = $request->input('sistema', 'Todos');
        $alcanceProductos = $request->input('alcance_productos', 'completo');
        $terminalesExcluidas = $request->has('terminales_excluidas')
            ? $this->normalizarTerminalesExcluidasReporteNuevoIncentivoV5($request->input('terminales_excluidas'))
            : $this->terminalesExcluidasIncentivoGuardadas();

        if ($modoCalculo === 'general') {
            $response = $this->reporteNuevoIncentivoV4($request);
            $payload = $response->getData(true);

            if (isset($payload['meta']) && is_array($payload['meta'])) {
                $payload['meta']['tramo_activo'] = 'incentivo_v5';
                $payload['meta']['modo_calculo'] = 'general';
                $payload['meta']['modo_calculo_label'] = 'General consolidado';
                $payload['meta']['resumen_empresas'] = $this->resumenEmpresasReporteNuevoIncentivoV5($request);
                $payload['meta']['terminales_excluidas'] = $terminalesExcluidas->all();
                $payload['meta']['terminales_excluidas_count'] = $terminalesExcluidas->count();
            }

            $payload = $this->normalizarPayloadEnteroReporteNuevoIncentivoV5($payload);
            $payload = $this->agregarHorasTotalesPayloadReporteNuevoIncentivoV5(
                $payload,
                $fechaIniSeleccionada,
                $fechaFinSeleccionada,
                $sistema
            );

            return response()->json($payload, $response->status());
        }

        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1G');

        $mesAnterior = Carbon::parse($fechaFinSeleccionada)->subMonthNoOverflow();
        $evalIni = $mesAnterior->copy()->startOfMonth()->toDateString();
        $evalFin = $mesAnterior->copy()->endOfMonth()->toDateString();
        $minDiasVenta = (int) $request->input('min_dias_venta', 1);
        $filtroCumplimiento = $request->input('filtro_cumplimiento', 'todos');

        $rangosPago = [];
        $rangosPagoInput = $request->input('rangos_pago');
        if (is_string($rangosPagoInput) && trim($rangosPagoInput) !== '') {
            $decoded = json_decode($rangosPagoInput, true);
            if (is_array($decoded)) {
                $rangosPago = collect($decoded)
                    ->map(function ($row) {
                        if (! is_array($row)) {
                            return null;
                        }

                        $desde = isset($row['desde']) ? (int) round((float) $row['desde']) : 0;
                        $hasta = array_key_exists('hasta', $row) && $row['hasta'] !== null && $row['hasta'] !== ''
                            ? (int) round((float) $row['hasta'])
                            : null;
                        $pago = isset($row['pago']) ? (float) $row['pago'] : 0;
                        $tipo = ($row['tipo'] ?? 'fijo') === 'porcentaje' ? 'porcentaje' : 'fijo';

                        if ($desde < 0 || ($hasta !== null && $hasta < 0) || $pago < 0 || ($hasta !== null && $desde > $hasta)) {
                            return null;
                        }

                        return compact('desde', 'hasta', 'pago', 'tipo');
                    })
                    ->filter()
                    ->sortBy('desde')
                    ->values()
                    ->all();
            }
        }

        if (empty($rangosPago)) {
            $rangosPago = [
                ['desde' => 100001, 'hasta' => 250000, 'pago' => 1000, 'tipo' => 'fijo'],
                ['desde' => 250001, 'hasta' => 400000, 'pago' => 2000, 'tipo' => 'fijo'],
                ['desde' => 400001, 'hasta' => 550000, 'pago' => 4000, 'tipo' => 'fijo'],
                ['desde' => 550001, 'hasta' => 700000, 'pago' => 6000, 'tipo' => 'fijo'],
                ['desde' => 700001, 'hasta' => 850000, 'pago' => 8000, 'tipo' => 'fijo'],
                ['desde' => 850001, 'hasta' => 1000000, 'pago' => 10000, 'tipo' => 'fijo'],
                ['desde' => 1000001, 'hasta' => null, 'pago' => 0.5, 'tipo' => 'porcentaje'],
            ];
        }

        $empresaKey = function ($empresa) {
            return strtolower(trim((string) ($empresa ?: 'Sin empresa')));
        };
        $rowKey = function ($cedula, $empresa) use ($empresaKey) {
            return trim((string) $cedula).'|'.$empresaKey($empresa);
        };
        $normalizarEmpresaNegocio = function ($empresa) {
            $texto = trim((string) $empresa);
            if ($texto === '' || strtolower($texto) === 'sin empresa') {
                return 'Agencias por asignar empresa';
            }

            $lower = strtolower($texto);
            if (strpos($lower, 'joselito') !== false) {
                return 'Grupo Joselito';
            }
            if (strpos($lower, 'negosur') !== false) {
                return 'Negosur';
            }

            return $texto;
        };
        $enteroMonto = fn ($value): int => (int) round((float) $value);

        $calcularIncentivo = function (int $ventas, int $dias) use ($rangosPago, $minDiasVenta) {
            if ($dias < $minDiasVenta) {
                return 0;
            }

            foreach ($rangosPago as $rango) {
                $desde = (int) round((float) ($rango['desde'] ?? 0));
                $hasta = $rango['hasta'] ?? null;

                if ($ventas >= $desde && ($hasta === null || $ventas <= (int) round((float) $hasta))) {
                    $pago = (float) ($rango['pago'] ?? 0);
                    $monto = ($rango['tipo'] ?? 'fijo') === 'porcentaje'
                        ? $ventas * ($pago / 100)
                        : $pago;

                    return (int) round($monto);
                }
            }

            return 0;
        };

        $buildVentasTerminalQuery = function (string $tabla, string $sistemaLabel, string $desde, string $hasta) use ($terminalesExcluidas, $alcanceProductos) {
            $query = DB::table("{$tabla} as v")
                ->selectRaw(
                    'v.cedula,
                    v.monto,
                    v.fecha,
                    TRIM(CAST(v.agencia_id AS CHAR)) AS terminal,
                    ? AS sistema',
                    [$sistemaLabel]
                )
                ->whereBetween('v.fecha', [$desde, $hasta])
                ->whereNotNull('v.cedula')
                ->where('v.cedula', '<>', '')
                ->whereNotNull('v.agencia_id')
                ->whereRaw("TRIM(CAST(v.agencia_id AS CHAR)) <> ''");

            if ($terminalesExcluidas->isNotEmpty()) {
                $query->whereNotIn(DB::raw('TRIM(CAST(v.agencia_id AS CHAR))'), $terminalesExcluidas->all());
            }

            return $this->applyProductScope($query, 'v.producto_id', $alcanceProductos);
        };

        $buildSourceQuery = function (string $desde, string $hasta) use ($sistema, $buildVentasTerminalQuery) {
            if ($sistema === 'Lotobet') {
                return $buildVentasTerminalQuery('vt_usuarios_bet', 'Lotobet', $desde, $hasta);
            }

            if ($sistema === 'Lotonet') {
                return $buildVentasTerminalQuery('vt_usuarios_net', 'Lotonet', $desde, $hasta);
            }

            return $buildVentasTerminalQuery('vt_usuarios_bet', 'Lotobet', $desde, $hasta)
                ->unionAll($buildVentasTerminalQuery('vt_usuarios_net', 'Lotonet', $desde, $hasta));
        };

        $rowsUltimoTerminal = DB::query()
            ->fromSub($buildSourceQuery($evalIni, $evalFin), 'ventas')
            ->selectRaw('ventas.cedula, ventas.terminal, SUM(ventas.monto) AS ventas_ultimo_mes')
            ->groupBy('ventas.cedula', 'ventas.terminal')
            ->get();

        $rowsMesActualTerminal = DB::query()
            ->fromSub($buildSourceQuery($fechaIniSeleccionada, $fechaFinSeleccionada), 'ventas')
            ->selectRaw('ventas.cedula, ventas.terminal, DATE(ventas.fecha) AS fecha_venta, SUM(ventas.monto) AS ventas_mes_actual')
            ->groupByRaw('ventas.cedula, ventas.terminal, DATE(ventas.fecha)')
            ->get();

        $terminales = $rowsUltimoTerminal->pluck('terminal')
            ->merge($rowsMesActualTerminal->pluck('terminal'))
            ->map(fn ($terminal) => trim((string) $terminal))
            ->filter()
            ->unique()
            ->values();

        $agenciaInfoByTerminal = [];
        foreach ($terminales->chunk(1000) as $terminalChunk) {
            DB::table('agencias')
                ->whereIn(DB::raw('TRIM(CAST(terminal AS CHAR))'), $terminalChunk->all())
                ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS terminal, COALESCE(NULLIF(TRIM(empresa), ''), 'Sin empresa') AS empresa, COALESCE(NULLIF(TRIM(nombre_agencia), ''), NULLIF(TRIM(agencia), ''), 'SIN AGENCIA') AS nombre_agencia")
                ->get()
                ->each(function ($row) use (&$agenciaInfoByTerminal) {
                    $agenciaInfoByTerminal[(string) $row->terminal] = [
                        'empresa' => (string) $row->empresa,
                        'nombre_agencia' => (string) $row->nombre_agencia,
                    ];
                });
        }

        $resolveEmpresa = function ($terminal) use (&$agenciaInfoByTerminal, $normalizarEmpresaNegocio) {
            return $normalizarEmpresaNegocio($agenciaInfoByTerminal[trim((string) $terminal)]['empresa'] ?? 'Agencias por asignar empresa');
        };
        $resolveAgenciaNombre = function ($terminal) use (&$agenciaInfoByTerminal) {
            return $agenciaInfoByTerminal[trim((string) $terminal)]['nombre_agencia'] ?? 'SIN AGENCIA';
        };

        $ultimoAgrupado = [];
        foreach ($rowsUltimoTerminal as $row) {
            $empresa = $resolveEmpresa($row->terminal);
            $key = $rowKey($row->cedula, $empresa);
            if (! isset($ultimoAgrupado[$key])) {
                $ultimoAgrupado[$key] = [
                    'cedula' => (string) $row->cedula,
                    'empresa' => $empresa,
                    'ventas_ultimo_mes' => 0,
                ];
            }

            $ultimoAgrupado[$key]['ventas_ultimo_mes'] += $enteroMonto($row->ventas_ultimo_mes);
        }

        $mesActualAgrupado = [];
        $ultimaVentaPorKey = [];
        foreach ($rowsMesActualTerminal as $row) {
            $empresa = $resolveEmpresa($row->terminal);
            $key = $rowKey($row->cedula, $empresa);
            if (! isset($mesActualAgrupado[$key])) {
                $mesActualAgrupado[$key] = [
                    'cedula' => (string) $row->cedula,
                    'empresa' => $empresa,
                    'ventas_mes_actual' => 0,
                    'dias' => [],
                ];
            }

            $mesActualAgrupado[$key]['ventas_mes_actual'] += $enteroMonto($row->ventas_mes_actual);
            $mesActualAgrupado[$key]['dias'][(string) $row->fecha_venta] = true;

            if (
                ! isset($ultimaVentaPorKey[$key])
                || strcmp((string) $row->fecha_venta, (string) $ultimaVentaPorKey[$key]['ultimo_dia_venta']) > 0
            ) {
                $ultimaVentaPorKey[$key] = [
                    'terminal' => trim((string) $row->terminal),
                    'nombre_agencia' => $resolveAgenciaNombre($row->terminal),
                    'ultimo_dia_venta' => (string) $row->fecha_venta,
                ];
            }
        }

        $rowsUltimoMes = collect(array_values($ultimoAgrupado))
            ->map(fn ($row) => (object) $row);
        $rowsMesActual = collect(array_values($mesActualAgrupado))
            ->map(function ($row) {
                return (object) [
                    'cedula' => $row['cedula'],
                    'empresa' => $row['empresa'],
                    'ventas_mes_actual' => $row['ventas_mes_actual'],
                    'dias_ventas_mes_actual' => count($row['dias']),
                ];
            });

        $ultimoMesByKey = $rowsUltimoMes->keyBy(fn ($row) => $rowKey($row->cedula, $row->empresa));
        $mesActualByKey = $rowsMesActual->keyBy(fn ($row) => $rowKey($row->cedula, $row->empresa));
        $keys = $ultimoMesByKey->keys()->merge($mesActualByKey->keys())->unique()->values();

        $rawData = $keys->map(function ($key) use ($ultimoMesByKey, $mesActualByKey, $calcularIncentivo) {
            $rowUltimoMes = $ultimoMesByKey->get($key);
            $rowMesActual = $mesActualByKey->get($key);
            $baseRow = $rowMesActual ?: $rowUltimoMes;
            $ventas = $rowUltimoMes ? (int) $rowUltimoMes->ventas_ultimo_mes : 0;
            $ventasMesActual = $rowMesActual ? (int) $rowMesActual->ventas_mes_actual : 0;
            $diasMesActual = $rowMesActual ? (int) $rowMesActual->dias_ventas_mes_actual : 0;
            $pagoEscala = $calcularIncentivo($ventasMesActual, $diasMesActual);

            return [
                'cedula' => (string) ($baseRow->cedula ?? ''),
                'empresa' => (string) ($baseRow->empresa ?? 'Agencias por asignar empresa'),
                'ventas_num' => $ventas,
                'ventas_mes_actual_num' => $ventasMesActual,
                'dias_ventas_mes_actual' => $diasMesActual,
                'cumple_bool' => $diasMesActual >= 1 && $pagoEscala > 0,
                'pago_escala_num' => $pagoEscala,
                'nuevo_incentivo_num' => $pagoEscala,
            ];
        })->sortByDesc('ventas_mes_actual_num')->values();

        if ($filtroCumplimiento === 'cumplidos') {
            $rawData = $rawData->where('cumple_bool', true)->values();
        } elseif ($filtroCumplimiento === 'no_cumplidos') {
            $rawData = $rawData->where('cumple_bool', false)->values();
        }

        $cedulasNormalizadas = $rawData->pluck('cedula')
            ->map(fn ($cedula) => preg_replace('/\D+/', '', (string) $cedula))
            ->filter()
            ->unique()
            ->values();
        $cedulaLookupKey = function ($cedula) {
            $digits = preg_replace('/\D+/', '', (string) $cedula);
            $normalized = ltrim($digits, '0');

            return $normalized === '' ? '0' : $normalized;
        };
        $empresaCompanyId = function ($empresa) {
            $text = mb_strtolower(trim((string) $empresa));
            if ($text === '') {
                return '';
            }

            if (str_contains($text, 'joselito') || str_contains($text, 'cjoselito') || str_contains($text, 'consorcio')) {
                return '168';
            }

            if (str_contains($text, 'negosur')) {
                return '169';
            }

            return '';
        };
        $empleadoLookupKey = function ($cedula, $empresa = '') use ($cedulaLookupKey, $empresaCompanyId) {
            $companyId = preg_replace('/\D+/', '', (string) $empresa);
            if ($companyId === '') {
                $companyId = $empresaCompanyId($empresa);
            }

            return $cedulaLookupKey($cedula).'|'.$companyId;
        };
        $empleadosPorCedula = collect();
        if ($cedulasNormalizadas->isNotEmpty()) {
            $empleadosPorCedula = DB::table('empleados')
                ->whereIn(DB::raw('CAST(cedula AS UNSIGNED)'), $cedulasNormalizadas->all())
                ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
                ->selectRaw('TRIM(CAST(companyid AS CHAR)) AS companyid')
                ->selectRaw("MAX(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')))) AS nombre")
                ->selectRaw('MAX(empleadoid) AS empleadoid')
                ->selectRaw("MAX(COALESCE(viapago, '')) AS viapago")
                ->selectRaw("MAX(COALESCE(ciudad, '')) AS ciudad")
                ->groupByRaw('CAST(cedula AS UNSIGNED), TRIM(CAST(companyid AS CHAR))')
                ->get()
                ->mapWithKeys(function ($row) use ($empleadoLookupKey) {
                    return [$empleadoLookupKey($row->cedula, $row->companyid) => $row];
                });
        }

        $horasTotalesPorCedula = $this->obtenerHorasTotalesReporteNuevoIncentivoV5(
            $fechaIniSeleccionada,
            $fechaFinSeleccionada,
            $sistema,
            $cedulasNormalizadas
        );

        $data = $rawData->map(function ($row) use ($empleadosPorCedula, $ultimaVentaPorKey, $rowKey, $cedulaLookupKey, $empleadoLookupKey, $horasTotalesPorCedula) {
            $cedulaLookup = $cedulaLookupKey($row['cedula'] ?? '');
            $cedulaKey = preg_replace('/\D+/', '', (string) ($row['cedula'] ?? ''));
            $empleado = $empleadosPorCedula->get($empleadoLookupKey($row['cedula'] ?? '', $row['empresa'] ?? ''));
            $nombre = trim((string) ($empleado->nombre ?? ''));
            $ultimaVenta = $ultimaVentaPorKey[$rowKey($cedulaKey, $row['empresa'] ?? 'Agencias por asignar empresa')] ?? [];

            return [
                'cedula' => $row['cedula'],
                'empleadoid' => (string) ($empleado->empleadoid ?? ''),
                'viapago' => (string) ($empleado->viapago ?? ''),
                'ciudad' => (string) ($empleado->ciudad ?? ''),
                'nombre' => $nombre !== '' ? $nombre : 'Actualizar en maestro de empleados',
                'ultima_terminal' => $ultimaVenta['terminal'] ?? '',
                'ultima_agencia_nombre' => $ultimaVenta['nombre_agencia'] ?? 'SIN AGENCIA',
                'ultimo_dia_venta' => $ultimaVenta['ultimo_dia_venta'] ?? '',
                'empresa' => $row['empresa'] ?? 'Agencias por asignar empresa',
                'ventas_ultimo_mes' => number_format($row['ventas_num'], 0, '.', ','),
                'ventas_mes_actual' => number_format($row['ventas_mes_actual_num'], 0, '.', ','),
                'dias_ventas_mes_actual' => $row['dias_ventas_mes_actual'],
                'horas_total' => number_format((float) ($horasTotalesPorCedula[$cedulaLookup] ?? 0), 2, '.', ''),
                'cumple_minimo' => $row['cumple_bool'] ? 'SI' : 'NO',
                'pago_escala' => number_format($row['pago_escala_num'], 0, '.', ','),
                'nuevo_incentivo' => number_format($row['nuevo_incentivo_num'], 0, '.', ','),
            ];
        })->values();

        $qualifiedRows = $rawData
            ->filter(fn ($row) => ($row['cumple_bool'] ?? false) && (float) ($row['nuevo_incentivo_num'] ?? 0) > 0)
            ->values();
        $qualifiedKeys = $qualifiedRows
            ->mapWithKeys(fn ($row) => [$rowKey($row['cedula'], $row['empresa']) => true]);
        $incentiveByKey = $qualifiedRows
            ->mapWithKeys(fn ($row) => [$rowKey($row['cedula'], $row['empresa']) => (float) $row['nuevo_incentivo_num']]);
        $employeeNamesByCedula = collect();
        if ($cedulasNormalizadas->isNotEmpty()) {
            $employeeNamesByCedula = DB::table('empleados')
                ->whereIn(DB::raw('CAST(cedula AS UNSIGNED)'), $cedulasNormalizadas->all())
                ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
                ->selectRaw("MAX(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')))) AS nombre")
                ->groupByRaw('CAST(cedula AS UNSIGNED)')
                ->get()
                ->mapWithKeys(function ($row) use ($cedulaLookupKey) {
                    return [$cedulaLookupKey($row->cedula) => (string) $row->nombre];
                });
        }

        $coordinatorValidAgencies = [];
        $coordinatorUserIncentiveAmounts = [];
        $coordinatorUserDetails = [];
        if ($qualifiedRows->isNotEmpty()) {
            $validCedulaTerminals = $rowsMesActualTerminal
                ->map(function ($row) use ($resolveEmpresa) {
                    return (object) [
                        'cedula' => preg_replace('/\D+/', '', (string) $row->cedula),
                        'terminal' => trim((string) $row->terminal),
                        'empresa' => $resolveEmpresa($row->terminal),
                    ];
                })
                ->unique(fn ($row) => $row->cedula.'|'.$row->terminal.'|'.strtolower($row->empresa))
                ->filter(fn ($row) => isset($qualifiedKeys[$rowKey($row->cedula, $row->empresa)]))
                ->values();

            $validTerminals = $validCedulaTerminals->pluck('terminal')->filter()->unique()->values();
            if ($validTerminals->isNotEmpty()) {
                $coordinatorAgencyRows = DB::table('coordinador_operador_agencia as coa')
                    ->join('agencias as a', 'a.id', '=', 'coa.agencia_id')
                    ->join('coordinador_operador as co', 'co.id', '=', 'coa.coordinador_operador_id')
                    ->where('co.puesto', 'coordinador')
                    ->whereIn(DB::raw('TRIM(CAST(a.terminal AS CHAR))'), $validTerminals->all())
                    ->selectRaw('coa.coordinador_operador_id, coa.agencia_id, TRIM(CAST(a.terminal AS CHAR)) AS terminal')
                    ->get();

                $coordinatorValidAgencies = $coordinatorAgencyRows
                    ->groupBy('coordinador_operador_id')
                    ->map(fn ($rows) => $rows->pluck('agencia_id')->unique()->count())
                    ->mapWithKeys(fn ($total, $coordinadorId) => [(string) $coordinadorId => (int) $total])
                    ->all();

                $coordinatorTerminals = $coordinatorAgencyRows
                    ->groupBy('coordinador_operador_id')
                    ->map(fn ($rows) => $rows->pluck('terminal')->unique()->flip())
                    ->all();

                $coordinatorKeys = [];
                foreach ($validCedulaTerminals as $row) {
                    $qualifiedKey = $rowKey($row->cedula, $row->empresa);
                    foreach ($coordinatorTerminals as $coordinadorId => $terminales) {
                        if (isset($terminales[$row->terminal])) {
                            $coordinatorKeys[(string) $coordinadorId][$qualifiedKey] = [
                                'cedula' => (string) $row->cedula,
                                'empresa' => (string) $row->empresa,
                            ];
                        }
                    }
                }

                foreach ($coordinatorKeys as $coordinadorId => $keysMap) {
                    $coordinatorUserIncentiveAmounts[(string) $coordinadorId] = collect($keysMap)
                        ->keys()
                        ->sum(fn ($key) => (float) ($incentiveByKey[$key] ?? 0));
                    $coordinatorUserDetails[(string) $coordinadorId] = collect($keysMap)
                        ->map(function ($item, $key) use ($incentiveByKey, $employeeNamesByCedula) {
                            $cedulaString = (string) ($item['cedula'] ?? '');
                            $empresa = (string) ($item['empresa'] ?? 'Agencias por asignar empresa');
                            $cedulaLookup = ltrim(preg_replace('/\D+/', '', $cedulaString), '0');
                            $cedulaLookup = $cedulaLookup === '' ? '0' : $cedulaLookup;

                            return [
                                'cedula' => $cedulaString,
                                'usuario' => trim(($employeeNamesByCedula[$cedulaLookup] ?? '').' | '.$empresa),
                                'incentivo' => (float) ($incentiveByKey[$key] ?? 0),
                            ];
                        })
                        ->sortByDesc('incentivo')
                        ->values()
                        ->all();
                }
            }
        }

        $resumenEmpresas = $rawData
            ->groupBy(fn ($row) => (string) ($row['empresa'] ?? 'Agencias por asignar empresa'))
            ->map(function ($rows, $empresa) {
                return [
                    'empresa' => (string) $empresa,
                    'total_vendido' => (int) round((float) $rows->sum('ventas_mes_actual_num')),
                    'total_incentivo' => (int) round((float) $rows->sum('nuevo_incentivo_num')),
                    'usuarios' => $rows->pluck('cedula')->unique()->count(),
                ];
            })
            ->values()
            ->all();

        $totalVendido = (int) round((float) $rawData->sum('ventas_mes_actual_num'));
        $totalIncentivo = (int) round((float) $rawData->sum('nuevo_incentivo_num'));
        $agenciasPorAsignar = collect($resumenEmpresas)
            ->firstWhere('empresa', 'Agencias por asignar empresa');

        return response()->json([
            'meta' => [
                'sistema' => $sistema,
                'fecha_ini' => $request->input('fecha_ini'),
                'fecha_fin' => $request->input('fecha_fin'),
                'eval_ini' => $evalIni,
                'eval_fin' => $evalFin,
                'min_dias_venta' => $minDiasVenta,
                'filtro_cumplimiento' => $filtroCumplimiento,
                'tramo_activo' => 'incentivo_v5',
                'modo_calculo' => 'separado_empresa',
                'modo_calculo_label' => 'Separado por empresa',
                'alcance_productos' => $alcanceProductos,
                'alcance_productos_label' => $alcanceProductos === 'no_tradicionales'
                    ? 'Solo productos no tradicionales'
                    : 'Reporte completo',
                'rangos_pago' => $rangosPago,
                'total_vendido' => $totalVendido,
                'total_vendido_ultimo_mes' => (int) round((float) $rawData->sum('ventas_num')),
                'total_vendido_mes_actual' => $totalVendido,
                'total_incentivo' => $totalIncentivo,
                'total_vendido_format' => number_format($totalVendido, 0, '.', ','),
                'total_vendido_ultimo_mes_format' => number_format((int) round((float) $rawData->sum('ventas_num')), 0, '.', ','),
                'total_vendido_mes_actual_format' => number_format($totalVendido, 0, '.', ','),
                'total_incentivo_format' => number_format($totalIncentivo, 0, '.', ','),
                'resumen_empresas' => $resumenEmpresas,
                'terminales_excluidas' => $terminalesExcluidas->all(),
                'terminales_excluidas_count' => $terminalesExcluidas->count(),
                'aviso_agencias_por_asignar_empresa' => $agenciasPorAsignar
                    ? 'Hay ventas en agencias sin empresa asignada. Filtra "Agencias por asignar empresa" y actualiza la columna empresa en agencias.'
                    : '',
                'coordinador_agencias_validas' => $coordinatorValidAgencies,
                'coordinador_monto_usuarios' => $coordinatorUserIncentiveAmounts,
                'coordinador_detalle_usuarios' => $coordinatorUserDetails,
            ],
            'data' => $data,
        ]);
    }

    private function resumenEmpresasReporteNuevoIncentivoV5(Request $request): array
    {
        $fechaIniSeleccionada = Carbon::parse($request->input('fecha_ini'))->toDateString();
        $fechaFinSeleccionada = Carbon::parse($request->input('fecha_fin'))->toDateString();
        $sistema = $request->input('sistema', 'Todos');
        $alcanceProductos = $request->input('alcance_productos', 'completo');
        $minDiasVenta = (int) $request->input('min_dias_venta', 1);
        $terminalesExcluidas = $request->has('terminales_excluidas')
            ? $this->normalizarTerminalesExcluidasReporteNuevoIncentivoV5($request->input('terminales_excluidas'))
            : $this->terminalesExcluidasIncentivoGuardadas();

        $rangosPago = [];
        $rangosPagoInput = $request->input('rangos_pago');
        if (is_string($rangosPagoInput) && trim($rangosPagoInput) !== '') {
            $decoded = json_decode($rangosPagoInput, true);
            if (is_array($decoded)) {
                $rangosPago = collect($decoded)
                    ->map(function ($row) {
                        if (! is_array($row)) {
                            return null;
                        }

                        $desde = isset($row['desde']) ? (int) round((float) $row['desde']) : 0;
                        $hasta = array_key_exists('hasta', $row) && $row['hasta'] !== null && $row['hasta'] !== ''
                            ? (int) round((float) $row['hasta'])
                            : null;
                        $pago = isset($row['pago']) ? (float) $row['pago'] : 0;
                        $tipo = ($row['tipo'] ?? 'fijo') === 'porcentaje' ? 'porcentaje' : 'fijo';

                        if ($desde < 0 || ($hasta !== null && $hasta < 0) || $pago < 0 || ($hasta !== null && $desde > $hasta)) {
                            return null;
                        }

                        return compact('desde', 'hasta', 'pago', 'tipo');
                    })
                    ->filter()
                    ->sortBy('desde')
                    ->values()
                    ->all();
            }
        }

        if (empty($rangosPago)) {
            $rangosPago = [
                ['desde' => 100001, 'hasta' => 250000, 'pago' => 1000, 'tipo' => 'fijo'],
                ['desde' => 250001, 'hasta' => 400000, 'pago' => 2000, 'tipo' => 'fijo'],
                ['desde' => 400001, 'hasta' => 550000, 'pago' => 4000, 'tipo' => 'fijo'],
                ['desde' => 550001, 'hasta' => 700000, 'pago' => 6000, 'tipo' => 'fijo'],
                ['desde' => 700001, 'hasta' => 850000, 'pago' => 8000, 'tipo' => 'fijo'],
                ['desde' => 850001, 'hasta' => 1000000, 'pago' => 10000, 'tipo' => 'fijo'],
                ['desde' => 1000001, 'hasta' => null, 'pago' => 0.5, 'tipo' => 'porcentaje'],
            ];
        }

        $normalizarEmpresaNegocio = function ($empresa) {
            $texto = trim((string) $empresa);
            if ($texto === '' || strtolower($texto) === 'sin empresa') {
                return 'Agencias por asignar empresa';
            }

            $lower = strtolower($texto);
            if (strpos($lower, 'joselito') !== false) {
                return 'Grupo Joselito';
            }
            if (strpos($lower, 'negosur') !== false) {
                return 'Negosur';
            }

            return $texto;
        };

        $enteroMonto = fn ($value): int => (int) round((float) $value);

        $calcularIncentivo = function (int $ventas, int $dias) use ($rangosPago, $minDiasVenta) {
            if ($dias < $minDiasVenta) {
                return 0;
            }

            foreach ($rangosPago as $rango) {
                $desde = (int) round((float) ($rango['desde'] ?? 0));
                $hasta = $rango['hasta'] ?? null;

                if ($ventas >= $desde && ($hasta === null || $ventas <= (int) round((float) $hasta))) {
                    $pago = (float) ($rango['pago'] ?? 0);
                    $monto = ($rango['tipo'] ?? 'fijo') === 'porcentaje'
                        ? $ventas * ($pago / 100)
                        : $pago;

                    return (int) round($monto);
                }
            }

            return 0;
        };

        $buildVentasTerminalQuery = function (string $tabla, string $sistemaLabel) use ($fechaIniSeleccionada, $fechaFinSeleccionada, $terminalesExcluidas, $alcanceProductos) {
            $query = DB::table("{$tabla} as v")
                ->selectRaw(
                    'v.cedula,
                    TRIM(CAST(v.agencia_id AS CHAR)) AS terminal,
                    DATE(v.fecha) AS fecha_venta,
                    SUM(v.monto) AS monto,
                    ? AS sistema',
                    [$sistemaLabel]
                )
                ->whereBetween('v.fecha', [$fechaIniSeleccionada, $fechaFinSeleccionada])
                ->whereNotNull('v.cedula')
                ->where('v.cedula', '<>', '')
                ->whereNotNull('v.agencia_id')
                ->whereRaw("TRIM(CAST(v.agencia_id AS CHAR)) <> ''")
                ->groupByRaw('v.cedula, TRIM(CAST(v.agencia_id AS CHAR)), DATE(v.fecha)');

            if ($terminalesExcluidas->isNotEmpty()) {
                $query->whereNotIn(DB::raw('TRIM(CAST(v.agencia_id AS CHAR))'), $terminalesExcluidas->all());
            }

            return $this->applyProductScope($query, 'v.producto_id', $alcanceProductos);
        };

        if ($sistema === 'Lotobet') {
            $sourceQuery = $buildVentasTerminalQuery('vt_usuarios_bet', 'Lotobet');
        } elseif ($sistema === 'Lotonet') {
            $sourceQuery = $buildVentasTerminalQuery('vt_usuarios_net', 'Lotonet');
        } else {
            $sourceQuery = $buildVentasTerminalQuery('vt_usuarios_bet', 'Lotobet')
                ->unionAll($buildVentasTerminalQuery('vt_usuarios_net', 'Lotonet'));
        }

        $ventasRows = DB::query()
            ->fromSub($sourceQuery, 'ventas')
            ->selectRaw('ventas.cedula, ventas.terminal, ventas.fecha_venta, SUM(ventas.monto) AS monto')
            ->groupBy('ventas.cedula', 'ventas.terminal', 'ventas.fecha_venta')
            ->get();

        $terminales = $ventasRows
            ->pluck('terminal')
            ->map(fn ($terminal) => trim((string) $terminal))
            ->filter()
            ->unique()
            ->values();

        $empresaByTerminal = [];
        foreach ($terminales->chunk(1000) as $terminalChunk) {
            DB::table('agencias')
                ->whereIn(DB::raw('TRIM(CAST(terminal AS CHAR))'), $terminalChunk->all())
                ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS terminal, COALESCE(NULLIF(TRIM(empresa), ''), 'Agencias por asignar empresa') AS empresa")
                ->get()
                ->each(function ($row) use (&$empresaByTerminal, $normalizarEmpresaNegocio) {
                    $empresaByTerminal[(string) $row->terminal] = $normalizarEmpresaNegocio($row->empresa);
                });
        }

        $rowsByCedulaEmpresa = [];
        foreach ($ventasRows as $row) {
            $empresa = $empresaByTerminal[trim((string) $row->terminal)] ?? 'Agencias por asignar empresa';
            $key = trim((string) $row->cedula).'|'.strtolower($empresa);

            if (! isset($rowsByCedulaEmpresa[$key])) {
                $rowsByCedulaEmpresa[$key] = [
                    'cedula' => trim((string) $row->cedula),
                    'empresa' => $empresa,
                    'ventas' => 0,
                    'dias' => [],
                ];
            }

            $rowsByCedulaEmpresa[$key]['ventas'] += $enteroMonto($row->monto);
            $rowsByCedulaEmpresa[$key]['dias'][(string) $row->fecha_venta] = true;
        }

        return collect($rowsByCedulaEmpresa)
            ->map(function ($row) use ($calcularIncentivo) {
                return [
                    'empresa' => $row['empresa'],
                    'cedula' => $row['cedula'],
                    'ventas' => (int) $row['ventas'],
                    'incentivo' => $calcularIncentivo((int) $row['ventas'], count($row['dias'])),
                ];
            })
            ->groupBy('empresa')
            ->map(function ($rows, $empresa) {
                return [
                    'empresa' => (string) $empresa,
                    'total_vendido' => (int) round((float) $rows->sum('ventas')),
                    'total_incentivo' => (int) round((float) $rows->sum('incentivo')),
                    'usuarios' => $rows->pluck('cedula')->unique()->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizarPayloadEnteroReporteNuevoIncentivoV5(array $payload): array
    {
        $camposData = [
            'ventas_ultimo_mes',
            'ventas_mes_actual',
            'pago_escala',
            'nuevo_incentivo',
            'total_incentivo',
            'total_a_pagar',
        ];

        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach ($payload['data'] as &$row) {
                if (! is_array($row)) {
                    continue;
                }

                foreach ($camposData as $campo) {
                    if (array_key_exists($campo, $row)) {
                        $monto = $this->enteroMontoReporteNuevoIncentivoV5($row[$campo]);
                        $row[$campo] = number_format($monto, 0, '.', ',');
                    }
                }
            }
            unset($row);
        }

        if (isset($payload['meta']) && is_array($payload['meta'])) {
            $camposMeta = [
                'total_vendido',
                'total_vendido_ultimo_mes',
                'total_vendido_mes_actual',
                'total_incentivo',
            ];

            foreach ($camposMeta as $campo) {
                if (array_key_exists($campo, $payload['meta'])) {
                    $payload['meta'][$campo] = $this->enteroMontoReporteNuevoIncentivoV5($payload['meta'][$campo]);
                }
            }

            $camposMetaFormato = [
                'total_vendido_format' => 'total_vendido',
                'total_vendido_ultimo_mes_format' => 'total_vendido_ultimo_mes',
                'total_vendido_mes_actual_format' => 'total_vendido_mes_actual',
                'total_incentivo_format' => 'total_incentivo',
            ];

            foreach ($camposMetaFormato as $campoFormato => $campoBase) {
                if (array_key_exists($campoBase, $payload['meta'])) {
                    $payload['meta'][$campoFormato] = number_format((int) $payload['meta'][$campoBase], 0, '.', ',');
                }
            }

            if (isset($payload['meta']['resumen_empresas']) && is_array($payload['meta']['resumen_empresas'])) {
                foreach ($payload['meta']['resumen_empresas'] as &$row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    foreach (['total_vendido', 'total_incentivo'] as $campo) {
                        if (array_key_exists($campo, $row)) {
                            $row[$campo] = $this->enteroMontoReporteNuevoIncentivoV5($row[$campo]);
                        }
                    }
                }
                unset($row);
            }

            if (isset($payload['meta']['coordinador_monto_usuarios']) && is_array($payload['meta']['coordinador_monto_usuarios'])) {
                foreach ($payload['meta']['coordinador_monto_usuarios'] as $key => $value) {
                    $payload['meta']['coordinador_monto_usuarios'][$key] = $this->enteroMontoReporteNuevoIncentivoV5($value);
                }
            }

            if (isset($payload['meta']['coordinador_detalle_usuarios']) && is_array($payload['meta']['coordinador_detalle_usuarios'])) {
                foreach ($payload['meta']['coordinador_detalle_usuarios'] as &$usuarios) {
                    if (! is_array($usuarios)) {
                        continue;
                    }

                    foreach ($usuarios as &$usuario) {
                        if (is_array($usuario) && array_key_exists('incentivo', $usuario)) {
                            $usuario['incentivo'] = $this->enteroMontoReporteNuevoIncentivoV5($usuario['incentivo']);
                        }
                    }
                    unset($usuario);
                }
                unset($usuarios);
            }
        }

        return $payload;
    }

    private function agregarHorasTotalesPayloadReporteNuevoIncentivoV5(array $payload, string $fechaIni, string $fechaFin, string $sistema): array
    {
        if (! isset($payload['data']) || ! is_array($payload['data']) || empty($payload['data'])) {
            return $payload;
        }

        $cedulas = collect($payload['data'])
            ->map(fn ($row) => preg_replace('/\D+/', '', (string) ($row['cedula'] ?? '')))
            ->filter()
            ->unique()
            ->values();

        $horasTotalesPorCedula = $this->obtenerHorasTotalesReporteNuevoIncentivoV5(
            $fechaIni,
            $fechaFin,
            $sistema,
            $cedulas
        );

        foreach ($payload['data'] as &$row) {
            if (! is_array($row)) {
                continue;
            }

            $cedula = preg_replace('/\D+/', '', (string) ($row['cedula'] ?? ''));
            $cedulaLookup = ltrim($cedula, '0');
            $cedulaLookup = $cedulaLookup === '' ? '0' : $cedulaLookup;
            $row['horas_total'] = number_format((float) ($horasTotalesPorCedula[$cedulaLookup] ?? 0), 2, '.', '');
        }
        unset($row);

        return $payload;
    }

    private function normalizarTerminalesExcluidasReporteNuevoIncentivoV5(mixed $value): Collection
    {
        $terminales = collect();

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $terminales = collect($decoded);
            } else {
                $terminales = $this->extraerTerminalesTextoReporteNuevoIncentivoV5($value);
            }
        } elseif (is_array($value)) {
            $terminales = collect($value);
        }

        return $terminales
            ->map(fn ($terminal) => trim((string) $terminal))
            ->filter(fn ($terminal) => $terminal !== '')
            ->unique()
            ->values();
    }

    private function terminalesExcluidasIncentivoGuardadas(): Collection
    {
        if (! Schema::hasTable('terminales_excluidas_incentivo')) {
            return collect();
        }

        return DB::table('terminales_excluidas_incentivo')
            ->selectRaw('TRIM(CAST(terminal AS CHAR)) AS terminal')
            ->whereNotNull('terminal')
            ->orderBy('terminal')
            ->pluck('terminal')
            ->map(fn ($terminal) => trim((string) $terminal))
            ->filter(fn ($terminal) => $terminal !== '')
            ->unique()
            ->values();
    }

    private function extraerTerminalesTextoReporteNuevoIncentivoV5(mixed $texto): Collection
    {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return collect();
        }

        return collect(preg_split('/[\r\n,;\t ]+/', $texto))
            ->map(fn ($terminal) => trim((string) $terminal))
            ->filter(fn ($terminal) => $terminal !== '')
            ->values();
    }

    private function valorColumnaReporteNuevoIncentivoV5(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $row)) {
                return $row[$alias];
            }

            $normalizedAlias = strtolower(str_replace([' ', '-', '.'], '_', $alias));
            foreach ($row as $key => $value) {
                $normalizedKey = strtolower(str_replace([' ', '-', '.'], '_', (string) $key));
                if ($normalizedKey === $normalizedAlias) {
                    return $value;
                }
            }
        }

        return null;
    }

    private function obtenerHorasTotalesReporteNuevoIncentivoV5(string $fechaIni, string $fechaFin, string $sistema, mixed $cedulas): Collection
    {
        $cedulas = collect($cedulas)
            ->map(fn ($cedula) => preg_replace('/\D+/', '', (string) $cedula))
            ->filter()
            ->unique()
            ->values();

        if ($cedulas->isEmpty()) {
            return collect();
        }

        $queries = [];

        if ($sistema === 'Todos' || $sistema === 'Lotonet') {
            $queries[] = DB::table('asistencias_net')
                ->selectRaw("REPLACE(identificacion, '-', '') AS cedula")
                ->selectRaw('SUM(GREATEST(TIMESTAMPDIFF(SECOND, entrada, salida), 0)) / 3600 AS horas')
                ->where('entrada', '>=', $fechaIni)
                ->whereRaw('entrada < DATE_ADD(?, INTERVAL 1 DAY)', [$fechaFin])
                ->whereNotNull('salida')
                ->whereIn(DB::raw('CAST(REPLACE(identificacion, "-", "") AS UNSIGNED)'), $cedulas->all())
                ->groupByRaw("REPLACE(identificacion, '-', '')");
        }

        if ($sistema === 'Todos' || $sistema === 'Lotobet') {
            $queries[] = DB::table('asistencias_bet')
                ->selectRaw("REPLACE(cedula, '-', '') AS cedula")
                ->selectRaw('SUM(GREATEST(TIMESTAMPDIFF(SECOND, primer_login, ultimo_login), 0)) / 3600 AS horas')
                ->whereBetween('fecha', [$fechaIni, $fechaFin])
                ->whereNotNull('primer_login')
                ->whereNotNull('ultimo_login')
                ->whereIn(DB::raw('CAST(REPLACE(cedula, "-", "") AS UNSIGNED)'), $cedulas->all())
                ->groupByRaw("REPLACE(cedula, '-', '')");
        }

        if (empty($queries)) {
            return collect();
        }

        $query = array_shift($queries);
        foreach ($queries as $unionQuery) {
            $query->unionAll($unionQuery);
        }

        return DB::query()
            ->fromSub($query, 'horas')
            ->selectRaw('CAST(cedula AS UNSIGNED) AS cedula')
            ->selectRaw('ROUND(SUM(COALESCE(horas, 0)), 2) AS horas_total')
            ->groupByRaw('CAST(cedula AS UNSIGNED)')
            ->get()
            ->mapWithKeys(function ($row) {
                $cedula = ltrim(preg_replace('/\D+/', '', (string) $row->cedula), '0');
                $cedula = $cedula === '' ? '0' : $cedula;

                return [$cedula => (float) $row->horas_total];
            });
    }

    private function enteroMontoReporteNuevoIncentivoV5(mixed $value): int
    {
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }

        return (int) round((float) $value);
    }

    public function faltantesReporteNuevoIncentivoV5(Request $request): JsonResponse
    {
        return $this->faltantesReporteNuevoIncentivoV4($request);
    }

    public function desvinculadosReporteNuevoIncentivoV5(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cedulas' => 'nullable|array',
            'cedulas.*' => 'nullable|string|max:50',
            'empleadoids' => 'nullable|array',
            'empleadoids.*' => 'nullable|string|max:50',
        ]);

        $cedulas = collect($validated['cedulas'] ?? [])
            ->map(fn ($cedula) => preg_replace('/\D+/', '', (string) $cedula))
            ->filter()
            ->unique()
            ->values();
        $empleadoIds = collect($validated['empleadoids'] ?? [])
            ->map(fn ($empleadoId) => trim((string) $empleadoId))
            ->filter()
            ->unique()
            ->values();

        if ($cedulas->isEmpty() && $empleadoIds->isEmpty()) {
            return response()->json([
                'total_desvinculados' => 0,
                'total_desactivados' => 0,
                'total_con_fecha_salida' => 0,
                'data' => [],
            ]);
        }

        $columnasEstado = collect(['activo', 'estatus'])
            ->filter(fn (string $columna): bool => Schema::hasColumn('empleados', $columna))
            ->values();
        $columnasFechaSalida = collect(['fechasalida', 'fecha_salida', 'fecha_egreso'])
            ->filter(fn (string $columna): bool => Schema::hasColumn('empleados', $columna))
            ->values();
        $tieneEliminacionLogica = Schema::hasColumn('empleados', 'deleted_at');

        if ($columnasEstado->isEmpty() && $columnasFechaSalida->isEmpty() && ! $tieneEliminacionLogica) {
            return response()->json([
                'total_desvinculados' => 0,
                'total_desactivados' => 0,
                'total_con_fecha_salida' => 0,
                'data' => [],
            ]);
        }

        $condicionEstadoActivo = function (string $alias) use ($columnasEstado): string {
            if ($columnasEstado->isEmpty()) {
                return '1 = 1';
            }

            return $columnasEstado
                ->map(fn (string $columna): string => "COALESCE({$alias}.{$columna}, 1) = 1")
                ->implode(' AND ');
        };
        $condicionEmpleadoActivo = function (string $alias) use (
            $columnasFechaSalida,
            $tieneEliminacionLogica,
            $condicionEstadoActivo
        ): string {
            $condiciones = collect([$condicionEstadoActivo($alias)]);

            $columnasFechaSalida->each(function (string $columna) use ($alias, $condiciones): void {
                $condiciones->push(
                    "(NULLIF(TRIM(CAST({$alias}.{$columna} AS CHAR)), '') IS NULL "
                    ."OR TRIM(CAST({$alias}.{$columna} AS CHAR)) = '0000-00-00')"
                );
            });

            if ($tieneEliminacionLogica) {
                $condiciones->push("{$alias}.deleted_at IS NULL");
            }

            return $condiciones->implode(' AND ');
        };
        $expresionesFechaSalida = $columnasFechaSalida
            ->map(fn (string $columna): string => "NULLIF(TRIM(CAST(empleados.{$columna} AS CHAR)), '')")
            ->push("''")
            ->implode(', ');

        $rows = DB::table('empleados as empleados')
            ->where(function ($query) use ($cedulas, $empleadoIds): void {
                if ($cedulas->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('CAST(empleados.cedula AS UNSIGNED)'), $cedulas->all());
                }

                if ($empleadoIds->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(CAST(empleados.empleadoid AS CHAR))'), $empleadoIds->all());
                }
            })
            ->whereRaw('NOT ('.$condicionEmpleadoActivo('empleados').')')
            ->whereNotExists(function ($query) use ($condicionEmpleadoActivo): void {
                $query->selectRaw('1')
                    ->from('empleados as empleados_activos')
                    ->whereRaw('CAST(empleados_activos.cedula AS UNSIGNED) = CAST(empleados.cedula AS UNSIGNED)')
                    ->whereRaw($condicionEmpleadoActivo('empleados_activos'));
            })
            ->selectRaw('CAST(empleados.cedula AS UNSIGNED) AS cedula')
            ->selectRaw('TRIM(CAST(empleados.empleadoid AS CHAR)) AS empleadoid')
            ->selectRaw('TRIM(CAST(empleados.companyid AS CHAR)) AS companyid')
            ->selectRaw("MAX(TRIM(CONCAT(COALESCE(empleados.nombres, ''), ' ', COALESCE(empleados.apellidos, '')))) AS nombre")
            ->selectRaw("MIN(CASE WHEN {$condicionEstadoActivo('empleados')} THEN 1 ELSE 0 END) AS activo")
            ->selectRaw("MAX(COALESCE({$expresionesFechaSalida})) AS fecha_salida")
            ->groupByRaw('CAST(empleados.cedula AS UNSIGNED), TRIM(CAST(empleados.empleadoid AS CHAR)), TRIM(CAST(empleados.companyid AS CHAR))')
            ->orderBy('nombre')
            ->get()
            ->map(function ($row) {
                $fechaSalida = trim((string) ($row->fecha_salida ?? ''));
                if ($fechaSalida === '0000-00-00') {
                    $fechaSalida = '';
                }

                $estaDesactivado = (int) ($row->activo ?? 1) === 0;
                $motivos = [];

                if ($estaDesactivado) {
                    $motivos[] = 'Desactivado';
                }

                if ($fechaSalida !== '') {
                    $motivos[] = 'Fecha de salida registrada';
                }

                $nombre = trim((string) ($row->nombre ?? ''));

                return [
                    'cedula' => (string) $row->cedula,
                    'empleadoid' => trim((string) ($row->empleadoid ?? '')),
                    'companyid' => trim((string) ($row->companyid ?? '')),
                    'nombre' => $nombre !== '' ? $nombre : 'Actualizar en maestro de empleados',
                    'estatus' => $motivos ? implode(' / ', $motivos) : 'Desvinculado',
                    'desactivado' => $estaDesactivado,
                    'fecha_salida' => $fechaSalida,
                ];
            })
            ->values();

        return response()->json([
            'total_desvinculados' => $rows->count(),
            'total_desactivados' => $rows->where('desactivado', true)->count(),
            'total_con_fecha_salida' => $rows->filter(fn ($row) => trim((string) ($row['fecha_salida'] ?? '')) !== '')->count(),
            'data' => $rows,
        ]);
    }
}
