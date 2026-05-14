<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    public function indexLotobet()
    {
        return view('dashboard.lotobet.ventas');
    }

    public function indexLotonet()
    {
        return view('dashboard.lotonet.ventas');
    }

    public function data(Request $request)
    {
        $plataforma = $request->get('plataforma', 'bet');
        $tabla = $plataforma === 'net' ? 'vt_usuarios_net' : 'vt_usuarios_bet';
        // dd($tabla);
        $agencia_id = $request->get('agencia_id', null);
        $empresaFilter = $this->normalizeEmpresaFilter((string) $request->get('empresa', 'todas'));

        $fecha_inicio = $request->get('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fecha_fin = $request->get('fecha_fin', Carbon::today()->format('Y-m-d'));

        // Validar formato fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
            $fecha_inicio = Carbon::today()->format('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
            $fecha_fin = Carbon::today()->format('Y-m-d');
        }

        $inicio = Carbon::createFromFormat('Y-m-d', $fecha_inicio)->startOfDay();
        $fin = Carbon::createFromFormat('Y-m-d', $fecha_fin)->endOfDay();
        $fechaReferenciaCero = Carbon::createFromFormat('Y-m-d', $fecha_fin)->toDateString();

        // Query para obtener datos de agencias con ventas
        $agenciasQuery = DB::table($tabla . ' as v')
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST(v.agencia_id AS CHAR))"))
            ->selectRaw("v.agencia_id, COUNT(DISTINCT v.agencia_id) as agencia_count, SUM(v.monto) as total")
            ->whereBetween('v.fecha', [$inicio, $fin])
            ->when($empresaFilter !== 'todas', function ($query) use ($empresaFilter) {
                $this->applyEmpresaFilter($query, $empresaFilter, 'a_emp.empresa');
            })
            ->groupBy('v.agencia_id')
            ->orderByRaw("SUM(v.monto) DESC");

        $agencias = $agenciasQuery->get();
        $totalAgencias = $agencias->count();
        
        $agenciasCatalogoSub = DB::table('agencias')
            ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS agencia_id")
            ->selectRaw("MAX(TRIM(COALESCE(nombre_agencia, ''))) AS nombre_agencia")
            ->whereNotNull('terminal')
            ->whereRaw("TRIM(CAST(terminal AS CHAR)) <> ''")
            ->when($empresaFilter !== 'todas', function ($query) use ($empresaFilter) {
                $this->applyEmpresaFilter($query, $empresaFilter, 'empresa');
            })
            ->groupByRaw("TRIM(CAST(terminal AS CHAR))");

        $ventasPorAgenciaSub = DB::table($tabla . ' as v')
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST(v.agencia_id AS CHAR))"))
            ->selectRaw("TRIM(CAST(v.agencia_id AS CHAR)) AS agencia_id")
            ->selectRaw("SUM(COALESCE(v.monto, 0)) AS total")
            ->whereNotNull('v.agencia_id')
            ->whereDate('v.fecha', $fechaReferenciaCero)
            ->when($empresaFilter !== 'todas', function ($query) use ($empresaFilter) {
                $this->applyEmpresaFilter($query, $empresaFilter, 'a_emp.empresa');
            })
            ->groupByRaw("TRIM(CAST(v.agencia_id AS CHAR))");

        $agenciasConTotales = DB::query()
            ->fromSub($agenciasCatalogoSub, 'a')
            ->leftJoinSub($ventasPorAgenciaSub, 'v', 'a.agencia_id', '=', 'v.agencia_id')
            ->selectRaw("a.agencia_id, a.nombre_agencia, COALESCE(v.total, 0) AS total")
            ->when($agencia_id, function ($query) use ($agencia_id) {
                $query->where('a.agencia_id', (string) $agencia_id);
            })
            ->orderBy('a.agencia_id')
            ->get();

        $agenciasCero = $agenciasConTotales
            ->filter(function ($row) {
                return (float) ($row->total ?? 0) <= 0;
            })
            ->map(function ($row) {
                $agenciaId = (string) ($row->agencia_id ?? '');
                $nombre = trim((string) ($row->nombre_agencia ?? ''));

                return [
                    'agencia_id' => $agenciaId,
                    'nombre_agencia' => $nombre !== '' ? $nombre : $agenciaId,
                    'total' => (float) ($row->total ?? 0),
                ];
            })
            ->values();
        $agenciasEnCero = $agenciasCero->count();

        // Expresión única para normalizar "tipo"
        $tipoExpr = "COALESCE(NULLIF(TRIM(c.tipo),''),'Sin tipo')";

        // 1) Subquery: aquí sí se agrupa
        $sub = DB::table($tabla . ' as v')
            ->leftJoin('catalogo_juegos as c', 'v.producto_id', '=', 'c.producto_id')
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST(v.agencia_id AS CHAR))"))
            ->selectRaw("$tipoExpr as tipo")
            ->selectRaw("SUM(v.monto) as total")
            ->selectRaw("COUNT(*) as transacciones")
            ->whereBetween('v.fecha', [$inicio, $fin])
            ->when($empresaFilter !== 'todas', function ($q) use ($empresaFilter) {
                $this->applyEmpresaFilter($q, $empresaFilter, 'a_emp.empresa');
            })
            ->when($agencia_id, function ($q) use ($agencia_id) {
                $q->where('v.agencia_id', $agencia_id);
            })
            // OJO: groupBy por la expresión (máxima compatibilidad con ONLY_FULL_GROUP_BY)
            ->groupByRaw($tipoExpr);

        // 2) Query externo: aquí solo se ordena (sin GROUP BY)
        $datos = DB::query()
            ->fromSub($sub, 'sub')
            ->orderByRaw("
                CASE
                    WHEN sub.tipo = 'tradicional' THEN 1
                    WHEN sub.tipo = 'no tradicional' THEN 2
                    ELSE 3
                END
            ")
            ->orderByDesc('sub.total')
            ->get();

        // Query para ventas por día separadas por tipo
        $ventasDiariasPorTipoQuery = DB::table($tabla . ' as v')
            ->leftJoin('catalogo_juegos as c', 'v.producto_id', '=', 'c.producto_id')
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST(v.agencia_id AS CHAR))"))
            ->selectRaw("DATE(v.fecha) as fecha, COALESCE(NULLIF(TRIM(c.tipo),''),'Sin tipo') as tipo, SUM(v.monto) as total")
            ->whereBetween('v.fecha', [$inicio, $fin])
            ->when($empresaFilter !== 'todas', function ($q) use ($empresaFilter) {
                $this->applyEmpresaFilter($q, $empresaFilter, 'a_emp.empresa');
            });

        if ($agencia_id) {
            $ventasDiariasPorTipoQuery->where('v.agencia_id', $agencia_id);
        }

        $ventasDiariasPorTipo = $ventasDiariasPorTipoQuery
            ->groupByRaw("DATE(v.fecha), tipo")
            ->orderBy('fecha')
            ->get();

        // Obtener fechas únicas para el gráfico
        $fechas = $ventasDiariasPorTipo->pluck('fecha')->unique()->sort()->values();

        // Ordenar tipos: tradicional, no tradicional, resto
        $tiposOrdenados = [];
        $tiposResto = [];

        foreach ($ventasDiariasPorTipo->pluck('tipo')->unique() as $tipo) {
            if ($tipo === 'tradicional') {
                array_unshift($tiposOrdenados, $tipo);
            } elseif ($tipo === 'no tradicional') {
                $tiposOrdenados[] = $tipo;
            } else {
                $tiposResto[] = $tipo;
            }
        }
        $tipos = collect(array_merge($tiposOrdenados, $tiposResto));

        // Calcular promedio diario del mes anterior por tipo
        $mesAnteriorInicio = $inicio->copy()->subMonth()->startOfMonth();
        $mesAnteriorFin = $inicio->copy()->subMonth()->endOfMonth();

        $ventasMesAnteriorPorTipoQuery = DB::table($tabla . ' as v')
            ->leftJoin('catalogo_juegos as c', 'v.producto_id', '=', 'c.producto_id')
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST(v.agencia_id AS CHAR))"))
            ->selectRaw("DATE(v.fecha) as fecha, COALESCE(NULLIF(TRIM(c.tipo),''),'Sin tipo') as tipo, SUM(v.monto) as total")
            ->whereBetween('v.fecha', [$mesAnteriorInicio, $mesAnteriorFin])
            ->when($empresaFilter !== 'todas', function ($q) use ($empresaFilter) {
                $this->applyEmpresaFilter($q, $empresaFilter, 'a_emp.empresa');
            });

        if ($agencia_id) {
            $ventasMesAnteriorPorTipoQuery->where('v.agencia_id', $agencia_id);
        }

        $ventasMesAnteriorPorTipo = $ventasMesAnteriorPorTipoQuery
            ->groupByRaw("DATE(v.fecha), COALESCE(NULLIF(TRIM(c.tipo),''),'Sin tipo')")
            ->get();

        // Calcular promedios por tipo del mes anterior
        $promediosPorTipoMesAnterior = [];
        foreach ($tipos as $tipo) {
            $ventasTipoMesAnterior = $ventasMesAnteriorPorTipo->where('tipo', $tipo);
            $promedioTipo = $ventasTipoMesAnterior->count() > 0
                ? $ventasTipoMesAnterior->sum('total') / $ventasTipoMesAnterior->count()
                : 0;
            $promediosPorTipoMesAnterior[$tipo] = $promedioTipo;
        }

        // Calcular promedio diario total del mes anterior
        $ventasMesAnteriorTotalQuery = DB::table($tabla)
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST({$tabla}.agencia_id AS CHAR))"))
            ->selectRaw("DATE(fecha) as fecha, SUM(monto) as total")
            ->whereBetween('fecha', [$mesAnteriorInicio, $mesAnteriorFin])
            ->when($empresaFilter !== 'todas', function ($q) use ($empresaFilter) {
                $this->applyEmpresaFilter($q, $empresaFilter, 'a_emp.empresa');
            });

        if ($agencia_id) {
            $ventasMesAnteriorTotalQuery->where('agencia_id', $agencia_id);
        }

        $ventasMesAnteriorTotal = $ventasMesAnteriorTotalQuery
            ->groupByRaw("DATE(fecha)")
            ->get();
        $promedioDiarioMesAnterior = $ventasMesAnteriorTotal->count() > 0
            ? $ventasMesAnteriorTotal->sum('total') / $ventasMesAnteriorTotal->count()
            : 0;

        // Construir datasets por tipo
        $datasetsPorTipo = [];
        $coloresDisponibes = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#4BC0C0'];

        foreach ($tipos as $index => $tipo) {
            $datosDelTipo = $ventasDiariasPorTipo->where('tipo', $tipo);
            $valores = [];

            foreach ($fechas as $fecha) {
                $venta = $datosDelTipo->where('fecha', $fecha)->first();
                $valores[] = $venta ? $venta->total : 0;
            }

            $datasetsPorTipo[] = [
                'label' => $tipo,
                'data' => $valores,
                'backgroundColor' => $coloresDisponibes[$index % count($coloresDisponibes)],
            ];
        }

        // Calcular total general
        $totalGeneral = $datos->sum('total');
        $transaccionesTotal = $datos->sum('transacciones');
        $ticketPromedio = $transaccionesTotal > 0 ? $totalGeneral / $transaccionesTotal : 0;

        // KPIs
        $kpis = [
            'total' => $totalGeneral,
            'transacciones' => $transaccionesTotal,
            'ticket_promedio' => $ticketPromedio,
            'total_agencias' => $totalAgencias,
            'agencias_en_cero' => $agenciasEnCero,
        ];

        // Chart data por tipo
        $chart = [
            'labels' => $datos->pluck('tipo')->toArray(),
            'values' => $datos->pluck('total')->toArray(),
        ];

        // Chart data por día
        $chartDiario = [
            'labels' => $fechas->toArray(),
            'datasets' => $datasetsPorTipo,
        ];

        // Chart data para gráfico de línea (totales por día)
        $ventasDiariasQuery = DB::table($tabla)
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST({$tabla}.agencia_id AS CHAR))"))
            ->selectRaw("DATE(fecha) as fecha, SUM(monto) as total")
            ->whereBetween('fecha', [$inicio, $fin])
            ->when($empresaFilter !== 'todas', function ($q) use ($empresaFilter) {
                $this->applyEmpresaFilter($q, $empresaFilter, 'a_emp.empresa');
            });

        if ($agencia_id) {
            $ventasDiariasQuery->where('agencia_id', $agencia_id);
        }

        $ventasDiarias = $ventasDiariasQuery
            ->groupByRaw("DATE(fecha)")
            ->orderBy('fecha')
            ->get();

        $chartDiarioLinea = [
            'labels' => $ventasDiarias->pluck('fecha')->toArray(),
            'values' => $ventasDiarias->pluck('total')->toArray(),
            'promedio_mes_anterior' => $promedioDiarioMesAnterior,
        ];

        // Agregar líneas de promedio del mes anterior por tipo al gráfico de barras diario
        foreach ($tipos as $index => $tipo) {
            $chartDiario['datasets'][] = [
                'label' => 'Promedio ' . $tipo . ' Mes Anterior: ' . number_format($promediosPorTipoMesAnterior[$tipo], 2),
                'data' => array_fill(0, count($fechas), $promediosPorTipoMesAnterior[$tipo]),
                'type' => 'line',
                'borderColor' => $coloresDisponibes[$index % count($coloresDisponibes)],
                'backgroundColor' => 'rgba(255, 215, 0, 0.1)',
                'borderWidth' => 2,
                'fill' => false,
                'pointRadius' => 0,
                'tension' => 0,
                'borderDash' => [5, 5], // Línea punteada para diferenciar
            ];
        }

        // Tabla data
        $tablaData = $datos->map(function ($item) use ($totalGeneral) {
            $porcentaje = $totalGeneral > 0 ? ($item->total / $totalGeneral) * 100 : 0;
            return [
                'tipo' => $item->tipo,
                'total' => $item->total,
                'transacciones' => $item->transacciones,
                'promedio' => $item->transacciones > 0 ? $item->total / $item->transacciones : 0,
                'porcentaje' => $porcentaje,
            ];
        })->toArray();

        return response()->json([
            'kpis' => $kpis,
            'chart' => $chart,
            'chart_diario' => $chartDiarioLinea,
            'chart_diario_tipos' => $chartDiario,
            'tabla' => $tablaData,
            'agencias' => $agencia_id ? [] : $agencias->map(function ($agencia) {
                return [
                    'agencia_id' => $agencia->agencia_id,
                    'total' => $agencia->total,
                ];
            })->toArray(),
            'agencias_cero' => $agencia_id ? [] : $agenciasCero->toArray(),
        ]);
    }

    public function exportAgenciasCeroPorDia(Request $request)
    {
        $plataforma = $request->get('plataforma', 'bet');
        $tabla = $plataforma === 'net' ? 'vt_usuarios_net' : 'vt_usuarios_bet';
        $empresaFilter = $this->normalizeEmpresaFilter((string) $request->get('empresa', 'todas'));

        $fechaInicio = (string) $request->get('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = (string) $request->get('fecha_fin', Carbon::today()->format('Y-m-d'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            $fechaInicio = Carbon::today()->format('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            $fechaFin = Carbon::today()->format('Y-m-d');
        }

        $inicio = Carbon::createFromFormat('Y-m-d', $fechaInicio)->startOfDay();
        $fin = Carbon::createFromFormat('Y-m-d', $fechaFin)->endOfDay();
        if ($inicio->greaterThan($fin)) {
            [$inicio, $fin] = [$fin->copy()->startOfDay(), $inicio->copy()->endOfDay()];
            [$fechaInicio, $fechaFin] = [$inicio->toDateString(), $fin->toDateString()];
        }

        $agenciasCatalogo = DB::table('agencias')
            ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS agencia_id")
            ->whereNotNull('terminal')
            ->whereRaw("TRIM(CAST(terminal AS CHAR)) <> ''")
            ->when($empresaFilter !== 'todas', function ($query) use ($empresaFilter) {
                $this->applyEmpresaFilter($query, $empresaFilter, 'empresa');
            })
            ->groupByRaw("TRIM(CAST(terminal AS CHAR))")
            ->orderByRaw("TRIM(CAST(terminal AS CHAR))")
            ->pluck('agencia_id')
            ->map(fn ($agenciaId) => (string) $agenciaId)
            ->values()
            ->all();

        $totalAgenciasCatalogo = count($agenciasCatalogo);

        $ventasPorDiaAgencia = DB::table($tabla . ' as v')
            ->leftJoin('agencias as a_emp', DB::raw("TRIM(CAST(a_emp.terminal AS CHAR))"), '=', DB::raw("TRIM(CAST(v.agencia_id AS CHAR))"))
            ->selectRaw("DATE(v.fecha) AS fecha")
            ->selectRaw("TRIM(CAST(v.agencia_id AS CHAR)) AS agencia_id")
            ->selectRaw("SUM(COALESCE(v.monto, 0)) AS total")
            ->whereNotNull('v.agencia_id')
            ->whereBetween('v.fecha', [$inicio, $fin])
            ->when($empresaFilter !== 'todas', function ($query) use ($empresaFilter) {
                $this->applyEmpresaFilter($query, $empresaFilter, 'a_emp.empresa');
            })
            ->groupByRaw("DATE(v.fecha), TRIM(CAST(v.agencia_id AS CHAR))")
            ->get();

        $totalesMap = [];
        foreach ($ventasPorDiaAgencia as $row) {
            $fecha = (string) ($row->fecha ?? '');
            $agenciaId = (string) ($row->agencia_id ?? '');
            if ($fecha === '' || $agenciaId === '') {
                continue;
            }
            $totalesMap[$fecha][$agenciaId] = (float) ($row->total ?? 0);
        }

        $lineas = [
            'fecha,cantidad_agencias_en_cero,total_agencias_catalogo',
        ];

        $cursor = Carbon::createFromFormat('Y-m-d', $fechaInicio);
        $limite = Carbon::createFromFormat('Y-m-d', $fechaFin);
        while ($cursor->lessThanOrEqualTo($limite)) {
            $fecha = $cursor->toDateString();
            $cantidadEnCero = 0;

            foreach ($agenciasCatalogo as $agenciaId) {
                $total = $totalesMap[$fecha][$agenciaId] ?? 0;
                if ((float) $total <= 0) {
                    $cantidadEnCero++;
                }
            }

            $lineas[] = implode(',', [
                $fecha,
                (string) $cantidadEnCero,
                (string) $totalAgenciasCatalogo,
            ]);

            $cursor->addDay();
        }

        $csv = implode("\r\n", $lineas) . "\r\n";
        $fileName = sprintf(
            'agencias_cero_por_dia_%s_%s_%s.csv',
            $plataforma,
            str_replace('-', '', $fechaInicio),
            str_replace('-', '', $fechaFin)
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function normalizeEmpresaFilter(string $empresa): string
    {
        $normalized = strtolower(trim($empresa));

        if (!in_array($normalized, ['todas', 'negosur', 'joselito'], true)) {
            return 'todas';
        }

        return $normalized;
    }

    private function applyEmpresaFilter($query, string $empresaFilter, string $column): void
    {
        if ($empresaFilter === 'todas') {
            return;
        }

        if ($empresaFilter === 'negosur') {
            $query->whereRaw(
                "REPLACE(LOWER(COALESCE({$column}, '')), ' ', '') LIKE ?",
                ['%negosur%']
            );
            return;
        }

        if ($empresaFilter === 'joselito') {
            $query->whereRaw(
                "REPLACE(LOWER(COALESCE({$column}, '')), ' ', '') LIKE ?",
                ['%joselito%']
            );
        }
    }
}
