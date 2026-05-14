<?php

namespace App\Http\Controllers;

use App\Exports\MetaIncentivoExport;
use App\Mail\MetaIncentivoMiniReporteMail;
use App\Models\CoordinadorOperador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class MetaIncentivoController extends Controller
{
    public function index(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        [$anio, $mes, $sistema, $coordinador, $cumplimiento, $fechaInicio, $fechaFin] = $this->resolveFiltros($request);
        $filtrosAplicados = $request->boolean('aplicar')
            || $request->hasAny(['anio', 'mes', 'sistema', 'coordinador', 'cumplimiento']);

        $reporte = $filtrosAplicados
            ? $this->buildReporte($fechaInicio, $fechaFin, $sistema, $coordinador, $cumplimiento)
            : new Collection();

        $sistemas = DB::table('agencias')
            ->select('sistema')
            ->whereNotNull('sistema')
            ->whereRaw("TRIM(sistema) <> ''")
            ->distinct()
            ->orderBy('sistema')
            ->pluck('sistema')
            ->values();

        $coordinadores = CoordinadorOperador::query()
            ->where('puesto', 'coordinador')
            ->selectRaw("TRIM(CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellido, ''))) as nombre_completo")
            ->whereRaw("TRIM(CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellido, ''))) <> ''")
            ->distinct()
            ->orderBy('nombre_completo')
            ->pluck('nombre_completo')
            ->values();

        return view('comercial.meta_incentivo', [
            'reporte' => $reporte,
            'anio' => $anio,
            'mes' => $mes,
            'sistema' => $sistema,
            'coordinador' => $coordinador,
            'cumplimiento' => $cumplimiento,
            'filtrosAplicados' => $filtrosAplicados,
            'sistemas' => $sistemas,
            'coordinadores' => $coordinadores,
            'fechaInicio' => $fechaInicio->toDateString(),
            'fechaFin' => $fechaFin->toDateString(),
        ]);
    }

    public function export(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        [$anio, $mes, $sistema, $coordinador, $cumplimiento, $fechaInicio, $fechaFin] = $this->resolveFiltros($request);
        $reporte = $this->buildReporte($fechaInicio, $fechaFin, $sistema, $coordinador, $cumplimiento);

        $fileName = sprintf(
            'meta_incentivo_%d_%02d%s%s%s.xlsx',
            $anio,
            $mes,
            $sistema !== '' ? '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $sistema) : '',
            $coordinador !== '' ? '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $coordinador) : '',
            $cumplimiento !== '' ? '_' . $cumplimiento : ''
        );

        return Excel::download(new MetaIncentivoExport($reporte), $fileName);
    }

    public function enviarMiniReporte(Request $request)
    {
        @set_time_limit(120);
        @ini_set('max_execution_time', '120');

        $inicioProceso = microtime(true);

        $validated = $request->validate([
            'anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'sistema' => ['nullable', 'string', 'max:100'],
            'coordinador' => ['required', 'string', 'max:150'],
            'cumplimiento' => ['nullable', 'in:,cumple,no-cumple'],
        ]);

        $coordinadorNombre = trim((string) $validated['coordinador']);
        if ($coordinadorNombre === '') {
            $mensaje = 'Debe seleccionar un coordinador para enviar el mini reporte.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $mensaje], 422);
            }

            return redirect()->back()->with('error', $mensaje);
        }

        $requestFiltros = new Request([
            'anio' => (int) $validated['anio'],
            'mes' => (int) $validated['mes'],
            'sistema' => (string) ($validated['sistema'] ?? ''),
            'coordinador' => $coordinadorNombre,
            'cumplimiento' => (string) ($validated['cumplimiento'] ?? ''),
        ]);

        [$anio, $mes, $sistema, $coordinador, $cumplimiento, $fechaInicio, $fechaFin] = $this->resolveFiltros($requestFiltros);
        $reporte = $this->buildReporte($fechaInicio, $fechaFin, $sistema, $coordinador, $cumplimiento);

        if ($reporte->isEmpty()) {
            $mensaje = 'No hay datos para enviar en el mini reporte con los filtros seleccionados.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $mensaje], 422);
            }

            return redirect()->back()->with('error', $mensaje);
        }

        $correos = CoordinadorOperador::query()
            ->where('puesto', 'coordinador')
            ->whereRaw("TRIM(CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellido, ''))) = ?", [$coordinadorNombre])
            ->whereNotNull('correo')
            ->whereRaw("TRIM(correo) <> ''")
            ->pluck('correo')
            ->map(fn($correo) => trim((string) $correo))
            ->filter()
            ->unique()
            ->values();

        if ($correos->isEmpty()) {
            $mensaje = 'El coordinador seleccionado no tiene un correo registrado en Coordinador / Operador.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $mensaje], 422);
            }

            return redirect()->back()->with('error', $mensaje);
        }

        $estadoPorAgencia = [];

        // Acumular totales por agencia para calcular porcentajes reales
        foreach ($reporte as $row) {
            $agenciaKey = (string) ($row->agencia_id ?? $row->nombre_agencia ?? '');
            if ($agenciaKey === '') {
                continue;
            }

            $metaIncremental = (float) ($row->meta_incremental ?? 0);
            $ventaPosterior = (float) ($row->total_venta_mes_posterior ?? 0);
            $cumple = $metaIncremental <= 0 || $ventaPosterior >= $metaIncremental;

            if (!array_key_exists($agenciaKey, $estadoPorAgencia)) {
                $estadoPorAgencia[$agenciaKey] = [
                    'agencia' => (string) ($row->nombre_agencia ?? $row->agencia_id ?? '-'),
                    'coordinador' => (string) ($row->coordinador ?? '-'),
                    'cumple' => true,
                    'meta_total' => 0.0,
                    'venta_total' => 0.0,
                    'codigo' => (string) ($row->agencia_id ?? ''),
                ];
            }

            if (!$cumple) {
                $estadoPorAgencia[$agenciaKey]['cumple'] = false;
            }

            $estadoPorAgencia[$agenciaKey]['meta_total'] += $metaIncremental;
            $estadoPorAgencia[$agenciaKey]['venta_total'] += $ventaPosterior;
        }

        $filas = collect($estadoPorAgencia)
            ->map(function ($item) {
                $meta = (float) ($item['meta_total'] ?? 0);
                $venta = (float) ($item['venta_total'] ?? 0);

                if ($meta <= 0) {
                    $porcentajeCumplido = 100.0;
                    $porcentajeFaltante = 0.0;
                } else {
                    $porcentajeCumplido = min(100.0, ($venta / $meta) * 100.0);
                    $porcentajeFaltante = max(0.0, 100.0 - $porcentajeCumplido);
                }

                $cumplimientoTexto = $item['cumple'] ? 'Cumple 100%' : sprintf('Falta %s%% para alcanzar el 100%%', number_format($porcentajeFaltante, 2));

                return [
                    'agencia' => $item['agencia'],
                    'codigo' => $item['codigo'] ?? '',
                    'coordinador' => $item['coordinador'],
                    'cumplimiento_meta' => $cumplimientoTexto,
                ];
            })
            ->values()
            ->all();

        // Calcular período mostrado en el mini reporte (siempre un mes adelante)
        $periodoMes = $mes + 1;
        $periodoAnio = $anio;
        if ($periodoMes > 12) {
            $periodoMes = 1;
            $periodoAnio++;
        }

        $data = [
            'coordinador' => $coordinadorNombre,
            'anio' => $anio,
            'mes' => $mes,
            'periodo_mes' => $periodoMes,
            'periodo_anio' => $periodoAnio,
            'fecha_inicio' => $fechaInicio->format('d/m/Y'),
            'fecha_fin' => $fechaFin->format('d/m/Y'),
            'filas' => $filas,
        ];

        try {
            Mail::to($correos->all())->send(new MetaIncentivoMiniReporteMail($data));
            Log::info('Enviando mini reporte Meta Incentivo', [
                'coordinador' => $coordinadorNombre,
                'correos' => $correos->all(),
                'total_filas_reporte' => $reporte->count(),
                'total_filas_correo' => count($filas),
            ]);
        } catch (Throwable $exception) {
            Log::error('Error enviando mini reporte Meta Incentivo', [
                'coordinador' => $coordinadorNombre,
                'correos' => $correos->all(),
                'error' => $exception->getMessage(),
            ]);

            $mensaje = 'No se pudo enviar el correo. Verifique la configuración SMTP e intente nuevamente.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $mensaje], 500);
            }

            return redirect()->back()->with('error', $mensaje);
        }

        Log::info('Mini reporte Meta Incentivo enviado', [
            'coordinador' => $coordinadorNombre,
            'total_filas_original' => $reporte->count(),
            'total_filas_correo' => count($filas),
            'duracion_segundos' => round(microtime(true) - $inicioProceso, 2),
        ]);

        $mensajeExito = 'Mini reporte enviado correctamente a: ' . $correos->implode(', ');
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $mensajeExito,
                'coordinador' => $coordinadorNombre,
                'total_filas' => count($filas),
            ]);
        }

        return redirect()->back()->with('success', $mensajeExito);
    }

    private function resolveFiltros(Request $request): array
    {
        $anio = (int) $request->query('anio', now()->year);
        $mes = (int) $request->query('mes', now()->month);
        $sistema = trim((string) $request->query('sistema', ''));
        $coordinador = trim((string) $request->query('coordinador', ''));
        $cumplimiento = trim((string) $request->query('cumplimiento', ''));

        $anio = $anio >= 2000 && $anio <= 2100 ? $anio : (int) now()->year;
        $mes = $mes >= 1 && $mes <= 12 ? $mes : (int) now()->month;

        $fechaCorte = Carbon::create($anio, $mes, 1);
        $fechaInicio = $fechaCorte->copy()->subMonths(2)->startOfMonth();
        $fechaFin = $fechaCorte->copy()->endOfMonth();

        if (!in_array($cumplimiento, ['', 'cumple', 'no-cumple'], true)) {
            $cumplimiento = '';
        }

        return [$anio, $mes, $sistema, $coordinador, $cumplimiento, $fechaInicio, $fechaFin];
    }

    private function buildReporte(Carbon $fechaInicio, Carbon $fechaFin, string $sistema, string $coordinador, string $cumplimiento = '')
    {
        $fechaPosteriorInicio = $fechaFin->copy()->addDay()->startOfMonth();
        $fechaPosteriorFin = $fechaPosteriorInicio->copy()->endOfMonth();

        $mesEstacionalidad = (int) $fechaPosteriorInicio->month;
        $anioEstacionalidad = (int) $fechaPosteriorInicio->year;

        $estacionalidadQuery = DB::table('estacionalidad')
            ->where('vigente', 1)
            ->where('mes', $mesEstacionalidad);

        if (DB::getSchemaBuilder()->hasColumn('estacionalidad', 'year')) {
            $estacionalidadQuery->where('year', $anioEstacionalidad);
        }

        $factorBase = (float) ($estacionalidadQuery->value('factor_base') ?? 1);
        if ($factorBase <= 0) {
            $factorBase = 1;
        }

        $factorBaseSql = number_format($factorBase, 6, '.', '');

        $ventasUnion = DB::table('vt_usuarios_net')
            ->selectRaw('TRIM(CAST(agencia_id AS CHAR)) AS agencia_id')
            ->selectRaw('TRIM(CAST(producto_id AS CHAR)) AS producto_id')
            ->selectRaw("NULLIF(TRIM(tipo), '') AS tipo_origen")
            ->selectRaw('COALESCE(monto, 0) AS monto')
            ->selectRaw('fecha')
            ->unionAll(
                DB::table('vt_usuarios_bet')
                    ->selectRaw('TRIM(CAST(agencia_id AS CHAR)) AS agencia_id')
                    ->selectRaw('TRIM(CAST(producto_id AS CHAR)) AS producto_id')
                    ->selectRaw("NULLIF(TRIM(tipo), '') AS tipo_origen")
                    ->selectRaw('COALESCE(monto, 0) AS monto')
                    ->selectRaw('fecha')
            );

        $fechaMin = $fechaInicio->toDateString();
        $fechaBaseMax = $fechaFin->toDateString();
        $fechaPosteriorMax = $fechaPosteriorFin->toDateString();
        $tipoAgrupadoSql = "CASE
            WHEN LOWER(TRIM(COALESCE(NULLIF(TRIM(cj.tipo), ''), NULLIF(TRIM(v.tipo_origen), ''), ''))) IN ('recarga', 'recargas', 'paquetico', 'paqueticos') THEN 'recarga'
            ELSE COALESCE(NULLIF(TRIM(cj.tipo), ''), NULLIF(TRIM(v.tipo_origen), ''), 'sin tipo')
        END";

        $baseQuery = DB::table('agencias as a')
            ->joinSub($ventasUnion, 'v', function ($join) {
                $join->whereRaw('TRIM(a.terminal) COLLATE utf8mb4_unicode_ci = TRIM(v.agencia_id) COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('catalogo_juegos as cj', function ($join) {
                $join->whereRaw('TRIM(cj.producto_id) COLLATE utf8mb4_unicode_ci = TRIM(v.producto_id) COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('coordinador_operador_agencia as coa', 'coa.agencia_id', '=', 'a.id')
            ->leftJoin('coordinador_operador as co', function ($join) {
                $join->on('co.id', '=', 'coa.coordinador_operador_id')
                    ->where('co.puesto', '=', 'coordinador');
            })
            ->selectRaw('a.terminal AS agencia_id')
            ->selectRaw('a.nombre_agencia')
            ->selectRaw("NULLIF(TRIM(GROUP_CONCAT(DISTINCT CASE WHEN co.id IS NOT NULL THEN TRIM(CONCAT(COALESCE(co.nombre, ''), ' ', COALESCE(co.apellido, ''))) END SEPARATOR ', ')), '') AS coordinador")
            ->selectRaw("{$tipoAgrupadoSql} AS tipo")
            ->selectRaw("SUM(CASE WHEN v.fecha BETWEEN '{$fechaMin}' AND '{$fechaBaseMax}' THEN v.monto ELSE 0 END) AS ventas_3_meses")
            ->selectRaw("(SUM(CASE WHEN v.fecha BETWEEN '{$fechaMin}' AND '{$fechaBaseMax}' THEN v.monto ELSE 0 END) / 3) AS promedio_3_meses")
            ->selectRaw("SUM(CASE WHEN v.fecha BETWEEN '{$fechaPosteriorInicio->toDateString()}' AND '{$fechaPosteriorMax}' THEN v.monto ELSE 0 END) AS total_venta_mes_posterior")
            ->whereNotNull('v.fecha')
            ->whereBetween('v.fecha', [$fechaMin, $fechaPosteriorMax])
            ->when($sistema !== '', function ($query) use ($sistema) {
                $query->where('a.sistema', $sistema);
            })
            ->when($coordinador !== '', function ($query) use ($coordinador) {
                $query->whereExists(function ($subQuery) use ($coordinador) {
                    $subQuery->selectRaw('1')
                        ->from('coordinador_operador_agencia as coa_filter')
                        ->join('coordinador_operador as co_filter', function ($join) {
                            $join->on('co_filter.id', '=', 'coa_filter.coordinador_operador_id')
                                ->where('co_filter.puesto', '=', 'coordinador');
                        })
                        ->whereColumn('coa_filter.agencia_id', 'a.id')
                        ->whereRaw("TRIM(CONCAT(COALESCE(co_filter.nombre, ''), ' ', COALESCE(co_filter.apellido, ''))) = ?", [$coordinador]);
                });
            })
            ->groupBy('a.terminal', 'a.nombre_agencia', DB::raw($tipoAgrupadoSql))
            ->havingRaw("SUM(CASE WHEN v.fecha BETWEEN '{$fechaMin}' AND '{$fechaBaseMax}' THEN v.monto ELSE 0 END) > 0");

        $reporte = DB::query()
            ->fromSub($baseQuery, 'r')
            ->leftJoin('niveles as n', function ($join) {
                $join->on('n.tipo_producto', '=', 'r.tipo')
                    ->whereRaw('r.promedio_3_meses BETWEEN n.rango_min AND n.rango_max');
            })
            ->selectRaw('r.*')
            ->selectRaw("({$factorBaseSql} * r.promedio_3_meses) AS ventas_base")
            ->selectRaw('COALESCE(n.nivel, "") AS nivel')
            ->selectRaw("IFNULL(IFNULL(n.incremento_fijo, (({$factorBaseSql} * r.promedio_3_meses) * n.incremento_porcentaje)), 0) AS incremetal")
            ->selectRaw("(({$factorBaseSql} * r.promedio_3_meses) + IFNULL(IFNULL(n.incremento_fijo, (({$factorBaseSql} * r.promedio_3_meses) * n.incremento_porcentaje)), 0)) AS meta_incremental")
            ->orderByDesc('r.ventas_3_meses')
            ->get();

        if ($cumplimiento === 'cumple') {
            return $reporte->filter(function ($row) {
                $meta = (float) ($row->meta_incremental ?? 0);
                $venta = (float) ($row->total_venta_mes_posterior ?? 0);
                return $meta <= 0 || $venta >= $meta;
            })->values();
        }

        if ($cumplimiento === 'no-cumple') {
            return $reporte->filter(function ($row) {
                $meta = (float) ($row->meta_incremental ?? 0);
                $venta = (float) ($row->total_venta_mes_posterior ?? 0);
                return $meta > 0 && $venta < $meta;
            })->values();
        }

        return $reporte;
    }
}
