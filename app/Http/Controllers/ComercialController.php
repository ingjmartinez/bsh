<?php

namespace App\Http\Controllers;

use App\Exports\AgenciaPlanExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ComercialController extends Controller
{
    public function index()
    {
        return view('comercial.index', [
            'kpis' => $this->getAcumuladosBet(),
        ]);
    }

    public function kpiVentas(Request $request)
    {
        $mes = $request->query('mes');

        if (!is_string($mes) || !preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        $metasDiarias = [
            'tradicional' => max(0, (float) $request->query('meta_tradicional', 0)),
            'no_tradicional' => max(0, (float) $request->query('meta_no_tradicional', 0)),
            'recargas' => max(0, (float) $request->query('meta_recargas', 0)),
        ];

        $kpis = $this->getAcumuladosBet($mes);
        $cumplimiento = $this->buildCumplimiento($kpis, $metasDiarias);
        $rentabilidadCargada = $request->boolean('cargar_rentabilidad');
        $resumenAgencias = $rentabilidadCargada ? $this->getResumenVentasAgenciaMensual($mes) : [];
        $agenciasPorTipo = $this->getCantidadAgenciasConVentaPorTipo($mes);

        return view('comercial.kpi-ventas', [
            'kpis' => $kpis,
            'metasDiarias' => $metasDiarias,
            'cumplimiento' => $cumplimiento,
            'resumenAgencias' => $resumenAgencias,
            'rentabilidadCargada' => $rentabilidadCargada,
            'agenciasPorTipo' => $agenciasPorTipo,
            'mesSeleccionado' => $mes,
        ]);
    }

    private function getCantidadAgenciasConVentaPorTipo(string $mes): array
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        [$year, $month] = explode('-', $mes);

        $rows = DB::table('vt_usuarios_bet')
            ->selectRaw('LOWER(TRIM(tipo)) AS tipo_normalizado')
            ->selectRaw('COUNT(DISTINCT TRIM(CAST(agencia_id AS CHAR))) AS total_agencias')
            ->whereNotNull('agencia_id')
            ->whereYear('fecha', (int) $year)
            ->whereMonth('fecha', (int) $month)
            ->groupBy(DB::raw('LOWER(TRIM(tipo))'))
            ->get();

        $resultado = [
            'tradicional' => 0,
            'no_tradicional' => 0,
            'recargas' => 0,
        ];

        foreach ($rows as $row) {
            $tipo = (string) ($row->tipo_normalizado ?? '');
            $cantidad = (int) ($row->total_agencias ?? 0);

            if ($tipo === 'tradicional') {
                $resultado['tradicional'] += $cantidad;
                continue;
            }

            if ($tipo === 'no tradicional' || $tipo === 'no_tradicional') {
                $resultado['no_tradicional'] += $cantidad;
                continue;
            }

            if ($tipo === 'recarga' || $tipo === 'recargas') {
                $resultado['recargas'] += $cantidad;
            }
        }

        return $resultado;
    }

    public function kpiVentasV(Request $request)
    {
        $fechaInput = $request->query('fecha')
            ?? $request->query('fecha_inicio')
            ?? $request->query('fecha_fin');

        $fechaObj = $this->parseFechaOrDefault($fechaInput, now());
        $fecha = $fechaObj->format('Y-m-d');
        $fechaSemanaAnterior = $fechaObj->copy()->subWeek()->format('Y-m-d');
        $fechaMesAnterior = $fechaObj->copy()->subMonthNoOverflow()->format('Y-m-d');
        $fechaAnioAnterior = $fechaObj->copy()->subYearNoOverflow()->format('Y-m-d');

        $indicadoresActual = $this->getIndicadoresBetPorRango($fecha, $fecha);
        $indicadoresSemanaAnterior = $this->getIndicadoresBetPorRango($fechaSemanaAnterior, $fechaSemanaAnterior);
        $indicadoresMesAnterior = $this->getIndicadoresBetPorRango($fechaMesAnterior, $fechaMesAnterior);
        $indicadoresAnioAnterior = $this->getIndicadoresBetPorRango($fechaAnioAnterior, $fechaAnioAnterior);

        $kpis = $this->extractMontosFromIndicadores($indicadoresActual);
        $ventasSemanaAnterior = $this->extractMontosFromIndicadores($indicadoresSemanaAnterior);
        $ventasMesAnterior = $this->extractMontosFromIndicadores($indicadoresMesAnterior);
        $ventasAnioAnterior = $this->extractMontosFromIndicadores($indicadoresAnioAnterior);
        $comparativasTabla = [
            $this->buildFilaComparativa('Fecha aplicada', $fecha, $indicadoresActual),
            $this->buildFilaComparativa('Ventas semana anterior (mismo día)', $fechaSemanaAnterior, $indicadoresSemanaAnterior),
            $this->buildFilaComparativa('Ventas mes anterior (mismo día)', $fechaMesAnterior, $indicadoresMesAnterior),
            $this->buildFilaComparativa('Ventas año anterior (mismo día)', $fechaAnioAnterior, $indicadoresAnioAnterior),
        ];

        return view('comercial.kpi-ventas-v', [
            'kpis' => $kpis,
            'fecha' => $fecha,
            'ventasSemanaAnterior' => $ventasSemanaAnterior,
            'fechaSemanaAnterior' => $fechaSemanaAnterior,
            'ventasMesAnterior' => $ventasMesAnterior,
            'fechaMesAnterior' => $fechaMesAnterior,
            'ventasAnioAnterior' => $ventasAnioAnterior,
            'fechaAnioAnterior' => $fechaAnioAnterior,
            'indicadoresActual' => $indicadoresActual,
            'indicadoresSemanaAnterior' => $indicadoresSemanaAnterior,
            'indicadoresMesAnterior' => $indicadoresMesAnterior,
            'indicadoresAnioAnterior' => $indicadoresAnioAnterior,
            'comparativasTabla' => $comparativasTabla,
        ]);
    }

    private function extractMontosFromIndicadores(array $indicadores): array
    {
        return [
            'tradicional' => (float) ($indicadores['tradicional']['monto'] ?? 0),
            'no_tradicional' => (float) ($indicadores['no_tradicional']['monto'] ?? 0),
            'recargas' => (float) ($indicadores['recargas']['monto'] ?? 0),
        ];
    }

    private function buildFilaComparativa(string $titulo, string $fecha, array $indicadores): array
    {
        return [
            'titulo' => $titulo,
            'fecha' => $fecha,
            'total_registros' => (int) ($indicadores['totales']['registros'] ?? 0),
            'total_agencias_con_venta' => (int) ($indicadores['totales']['agencias_con_venta'] ?? 0),
            'promedio_venta_general' => (float) ($indicadores['totales']['promedio_venta_general'] ?? 0),
            'total_tradicional' => (float) ($indicadores['tradicional']['monto'] ?? 0),
            'total_no_tradicional' => (float) ($indicadores['no_tradicional']['monto'] ?? 0),
            'total_recargas' => (float) ($indicadores['recargas']['monto'] ?? 0),
            'total_general' => (float) ($indicadores['totales']['monto_general'] ?? 0),
            'agencias_tradicional' => (int) ($indicadores['tradicional']['agencias'] ?? 0),
            'agencias_no_tradicional' => (int) ($indicadores['no_tradicional']['agencias'] ?? 0),
            'agencias_recargas' => (int) ($indicadores['recargas']['agencias'] ?? 0),
            'promedio_tradicional' => (float) ($indicadores['tradicional']['promedio'] ?? 0),
            'promedio_no_tradicional' => (float) ($indicadores['no_tradicional']['promedio'] ?? 0),
            'promedio_recargas' => (float) ($indicadores['recargas']['promedio'] ?? 0),
        ];
    }

    private function getValidacionVentasPorRango(string $fechaInicio, string $fechaFin): array
    {
        return DB::table('vt_usuarios_bet')
            ->selectRaw('fecha')
            ->selectRaw('COUNT(*) AS total_registros')
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) = 'tradicional' THEN COALESCE(monto, 0) ELSE 0 END) AS total_tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('no tradicional','no_tradicional') THEN COALESCE(monto, 0) ELSE 0 END) AS total_no_tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('recarga','recargas') THEN COALESCE(monto, 0) ELSE 0 END) AS total_recargas")
            ->selectRaw('SUM(COALESCE(monto, 0)) AS total_general')
            ->whereNotNull('fecha')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(function ($row) {
                return [
                    'fecha' => (string) ($row->fecha ?? ''),
                    'total_registros' => (int) ($row->total_registros ?? 0),
                    'total_tradicional' => (float) ($row->total_tradicional ?? 0),
                    'total_no_tradicional' => (float) ($row->total_no_tradicional ?? 0),
                    'total_recargas' => (float) ($row->total_recargas ?? 0),
                    'total_general' => (float) ($row->total_general ?? 0),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getAcumuladosBetPorRango(string $fechaInicio, string $fechaFin): array
    {
        $acumulados = DB::table('vt_usuarios_bet')
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) = 'tradicional' THEN monto ELSE 0 END) AS tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('no tradicional','no_tradicional') THEN monto ELSE 0 END) AS no_tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('recarga','recargas') THEN monto ELSE 0 END) AS recargas")
            ->whereNotNull('fecha')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->first();

        return [
            'tradicional' => (float) ($acumulados->tradicional ?? 0),
            'no_tradicional' => (float) ($acumulados->no_tradicional ?? 0),
            'recargas' => (float) ($acumulados->recargas ?? 0),
        ];
    }

    private function getIndicadoresBetPorRango(string $fechaInicio, string $fechaFin): array
    {
        $row = DB::table('vt_usuarios_bet')
            ->selectRaw("COUNT(*) AS total_registros")
            ->selectRaw("SUM(COALESCE(monto, 0)) AS monto_general")
            ->selectRaw("COUNT(DISTINCT CASE WHEN COALESCE(monto, 0) > 0 AND agencia_id IS NOT NULL THEN TRIM(CAST(agencia_id AS CHAR)) END) AS agencias_con_venta")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) = 'tradicional' THEN COALESCE(monto, 0) ELSE 0 END) AS monto_tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('no tradicional','no_tradicional') THEN COALESCE(monto, 0) ELSE 0 END) AS monto_no_tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('recarga','recargas') THEN COALESCE(monto, 0) ELSE 0 END) AS monto_recargas")
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(TRIM(tipo)) = 'tradicional' AND COALESCE(monto, 0) > 0 AND agencia_id IS NOT NULL THEN TRIM(CAST(agencia_id AS CHAR)) END) AS agencias_tradicional")
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(TRIM(tipo)) IN ('no tradicional','no_tradicional') AND COALESCE(monto, 0) > 0 AND agencia_id IS NOT NULL THEN TRIM(CAST(agencia_id AS CHAR)) END) AS agencias_no_tradicional")
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(TRIM(tipo)) IN ('recarga','recargas') AND COALESCE(monto, 0) > 0 AND agencia_id IS NOT NULL THEN TRIM(CAST(agencia_id AS CHAR)) END) AS agencias_recargas")
            ->whereNotNull('fecha')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->first();

        $tradMonto = (float) ($row->monto_tradicional ?? 0);
        $noTradMonto = (float) ($row->monto_no_tradicional ?? 0);
        $recargasMonto = (float) ($row->monto_recargas ?? 0);

        $tradAgencias = (int) ($row->agencias_tradicional ?? 0);
        $noTradAgencias = (int) ($row->agencias_no_tradicional ?? 0);
        $recargasAgencias = (int) ($row->agencias_recargas ?? 0);
        $totalRegistros = (int) ($row->total_registros ?? 0);
        $montoGeneral = (float) ($row->monto_general ?? 0);
        $agenciasConVenta = (int) ($row->agencias_con_venta ?? 0);

        return [
            'tradicional' => [
                'monto' => $tradMonto,
                'agencias' => $tradAgencias,
                'promedio' => $tradAgencias > 0 ? $tradMonto / $tradAgencias : 0,
            ],
            'no_tradicional' => [
                'monto' => $noTradMonto,
                'agencias' => $noTradAgencias,
                'promedio' => $noTradAgencias > 0 ? $noTradMonto / $noTradAgencias : 0,
            ],
            'recargas' => [
                'monto' => $recargasMonto,
                'agencias' => $recargasAgencias,
                'promedio' => $recargasAgencias > 0 ? $recargasMonto / $recargasAgencias : 0,
            ],
            'totales' => [
                'registros' => $totalRegistros,
                'monto_general' => $montoGeneral,
                'agencias_con_venta' => $agenciasConVenta,
                'promedio_venta_general' => $agenciasConVenta > 0 ? $montoGeneral / $agenciasConVenta : 0,
            ],
        ];
    }

    private function parseFechaOrDefault($fecha, Carbon $default): Carbon
    {
        if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $default->copy();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();
        } catch (\Throwable $e) {
            return $default->copy();
        }
    }

    private function getAcumuladosBet(?string $mes = null, ?string $dia = null): array
    {
        $query = DB::table('vt_usuarios_bet')
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) = 'tradicional' THEN monto ELSE 0 END) AS tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('no tradicional','no_tradicional') THEN monto ELSE 0 END) AS no_tradicional")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(tipo)) IN ('recarga','recargas') THEN monto ELSE 0 END) AS recargas");

        if (is_string($dia) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia)) {
            $query->whereDate('fecha', $dia);
        } elseif (is_string($mes) && preg_match('/^\d{4}-\d{2}$/', $mes)) {
            [$year, $month] = explode('-', $mes);
            $query->whereYear('fecha', (int) $year)
                ->whereMonth('fecha', (int) $month);
        }

        $acumulados = $query->first();

        return [
            'tradicional' => (float) ($acumulados->tradicional ?? 0),
            'no_tradicional' => (float) ($acumulados->no_tradicional ?? 0),
            'recargas' => (float) ($acumulados->recargas ?? 0),
        ];
    }

    private function buildCumplimiento(array $kpis, array $metasDiarias): array
    {
        $productos = ['tradicional', 'no_tradicional', 'recargas'];
        $resultado = [];

        foreach ($productos as $producto) {
            $metaMensual = max(0, (float) ($metasDiarias[$producto] ?? 0)) * 30;
            $acumulado = max(0, (float) ($kpis[$producto] ?? 0));
            $faltante = max(0, $metaMensual - $acumulado);
            $pctFaltante = $metaMensual > 0 ? ($faltante / $metaMensual) * 100 : 0;

            $resultado[$producto] = [
                'meta_mensual' => $metaMensual,
                'faltante' => $faltante,
                'pct_faltante' => $pctFaltante,
            ];
        }

        return $resultado;
    }

    private function getResumenVentasAgenciaMensual(string $mes): array
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        [$year, $month] = explode('-', $mes);
        $fechaInicio = sprintf('%s-%s-01', $year, $month);
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));

        $ventasRows = DB::table('vt_usuarios_bet')
            ->selectRaw("TRIM(CAST(agencia_id AS CHAR)) AS agencia")
            ->selectRaw('SUM(COALESCE(monto, 0)) AS total_vendido')
            ->whereNotNull('agencia_id')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw("TRIM(CAST(agencia_id AS CHAR))"))
            ->orderByDesc('total_vendido')
            ->get();

        $premiosAotra = DB::table('pagos_aotra_empresa_bet')
            ->selectRaw("TRIM(CAST(agencia_id AS CHAR)) AS agencia")
            ->selectRaw('COALESCE(monto, 0) AS monto')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        $premiosMisma = DB::table('pagos_misma_empresa_bet')
            ->selectRaw("TRIM(CAST(agencia_id AS CHAR)) AS agencia")
            ->selectRaw('COALESCE(monto, 0) AS monto')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        $premiosRows = DB::query()
            ->fromSub($premiosAotra->unionAll($premiosMisma), 't')
            ->selectRaw('agencia, SUM(monto) AS premios_pagados')
            ->groupBy('agencia')
            ->get();

        $premiosByAgencia = $premiosRows
            ->mapWithKeys(function ($row) {
                $agencia = (string) ($row->agencia ?? '');
                return [$agencia => (float) ($row->premios_pagados ?? 0)];
            })
            ->toArray();

        $nombresAgenciaByTerminal = DB::table('agencias')
            ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS terminal")
            ->selectRaw('TRIM(COALESCE(nombre_agencia, "")) AS nombre_agencia')
            ->whereNotNull('terminal')
            ->get()
            ->mapWithKeys(function ($row) {
                $terminal = (string) ($row->terminal ?? '');
                $nombre = (string) ($row->nombre_agencia ?? '');

                return [$terminal => $nombre];
            })
            ->toArray();

        return $ventasRows->map(function ($row) use ($premiosByAgencia, $nombresAgenciaByTerminal) {
            $agencia = (string) ($row->agencia ?? 'SIN AGENCIA');
            $totalVendido = (float) ($row->total_vendido ?? 0);
            $premiosPagados = (float) ($premiosByAgencia[$agencia] ?? 0);
            $nombreAgencia = trim((string) ($nombresAgenciaByTerminal[$agencia] ?? ''));

            return [
                'agencia' => $agencia,
                'terminal' => $agencia,
                'nombre_agencia' => $nombreAgencia !== '' ? $nombreAgencia : $agencia,
                'total_vendido' => $totalVendido,
                'premios_pagados' => $premiosPagados,
            ];
        })->toArray();
    }

    public function agenciaPlan(Request $request)
    {
        $contexto = $this->buildAgenciaPlanData($request);

        return view('comercial.agencia_plan', $contexto);
    }

    public function agenciaPlanExport(Request $request)
    {
        $contexto = $this->buildAgenciaPlanData($request, true);

        $fileName = sprintf(
            'agencia_plan_%s_%s.xlsx',
            str_replace('-', '', (string) ($contexto['mes'] ?? now()->format('Y-m'))),
            strtolower((string) ($contexto['sistema'] ?? 'sistema'))
        );

        return Excel::download(
            new AgenciaPlanExport(
                $contexto['filas'] ?? collect(),
                (string) ($contexto['sistema'] ?? ''),
                (string) ($contexto['rangoInicio'] ?? ''),
                (string) ($contexto['rangoFin'] ?? '')
            ),
            $fileName
        );
    }

    private function buildAgenciaPlanData(Request $request, bool $forzarConsulta = false): array
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $mes = trim((string) $request->query('mes', now()->format('Y-m')));
        if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        $sistemas = ['Lotonet', 'Lotobet'];
        $sistema = trim((string) $request->query('sistema', 'Lotonet'));
        if (!in_array($sistema, $sistemas, true)) {
            $sistema = 'Lotonet';
        }

        $tablaPorSistema = [
            'Lotonet' => 'vt_usuarios_net',
            'Lotobet' => 'vt_usuarios_bet',
        ];

        [$anioSeleccionado, $mesSeleccionado] = explode('-', $mes);
        $fechaInicioMes = Carbon::create((int) $anioSeleccionado, (int) $mesSeleccionado, 1)->startOfMonth();
        $fechaFinMes = $fechaInicioMes->copy()->endOfMonth();

        $tabla = $tablaPorSistema[$sistema];
        $filtrosAplicados = $forzarConsulta || $request->boolean('aplicar');
        $fechaCorteData = null;

        $diasObjetivo = 90;
        $rangoInicio = null;
        $rangoFin = null;
        $filas = collect();

        if ($filtrosAplicados) {
            $agenciasDelMesSubquery = DB::table("{$tabla} as vm")
                ->selectRaw('DISTINCT TRIM(CAST(vm.agencia_id AS CHAR)) AS agencia_id')
                ->whereNotNull('vm.agencia_id')
                ->whereBetween('vm.fecha', [$fechaInicioMes->toDateString(), $fechaFinMes->toDateString()]);

            $fechaCorteData = DB::table($tabla)
                ->whereNotNull('agencia_id')
                ->whereBetween('fecha', [$fechaInicioMes->toDateString(), $fechaFinMes->toDateString()])
                ->orderByDesc('fecha')
                ->value('fecha');

            if (!empty($fechaCorteData)) {
                $rangoFin = Carbon::parse($fechaCorteData)->toDateString();

                $ventasDiarias = DB::table("{$tabla} as v")
                    ->joinSub($agenciasDelMesSubquery, 'am', function ($join) {
                        $join->whereRaw('TRIM(CAST(v.agencia_id AS CHAR)) = am.agencia_id');
                    })
                    ->selectRaw('TRIM(CAST(v.agencia_id AS CHAR)) AS agencia_id')
                    ->selectRaw('DATE(v.fecha) AS fecha_dia')
                    ->selectRaw('SUM(COALESCE(v.monto, 0)) AS monto_dia')
                    ->whereNotNull('v.agencia_id')
                    ->whereDate('v.fecha', '<=', $rangoFin)
                    ->groupBy(
                        DB::raw('TRIM(CAST(v.agencia_id AS CHAR))'),
                        DB::raw('DATE(v.fecha)')
                    );

                $ventasRankeadas = DB::query()
                    ->fromSub($ventasDiarias, 'd')
                    ->selectRaw('d.agencia_id')
                    ->selectRaw('d.fecha_dia')
                    ->selectRaw('d.monto_dia')
                    ->selectRaw('ROW_NUMBER() OVER (PARTITION BY d.agencia_id ORDER BY d.fecha_dia DESC) AS rn');

                $resumenAgencias = DB::query()
                    ->fromSub($ventasRankeadas, 'r')
                    ->selectRaw('r.agencia_id')
                    ->selectRaw("SUM(CASE WHEN r.rn <= {$diasObjetivo} THEN 1 ELSE 0 END) AS dias_con_venta")
                    ->selectRaw("SUM(CASE WHEN r.rn <= {$diasObjetivo} THEN r.monto_dia ELSE 0 END) AS monto_90_dias")
                    ->selectRaw("MIN(CASE WHEN r.rn <= {$diasObjetivo} THEN r.fecha_dia ELSE NULL END) AS fecha_inicio_efectiva")
                    ->groupBy('r.agencia_id')
                    ->orderByDesc('monto_90_dias')
                    ->get();

                $nombresAgencia = DB::table('agencias')
                    ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS terminal")
                    ->selectRaw("TRIM(COALESCE(nombre_agencia, '')) AS nombre_agencia")
                    ->whereNotNull('terminal')
                    ->get()
                    ->mapWithKeys(function ($row) {
                        return [
                            (string) ($row->terminal ?? '') => (string) ($row->nombre_agencia ?? ''),
                        ];
                    });

                $filas = $resumenAgencias
                    ->map(function ($row) use ($nombresAgencia, $diasObjetivo) {
                        $agenciaId = (string) ($row->agencia_id ?? '');
                        $diasConVenta = (int) ($row->dias_con_venta ?? 0);
                        $diasFaltantes = max(0, $diasObjetivo - $diasConVenta);
                        $aplica = $diasConVenta >= $diasObjetivo;
                        $nombreAgencia = trim((string) ($nombresAgencia[$agenciaId] ?? ''));

                        return (object) [
                            'agencia_id' => $agenciaId,
                            'nombre_agencia' => $nombreAgencia !== '' ? $nombreAgencia : $agenciaId,
                            'dias_con_venta' => $diasConVenta,
                            'dias_faltantes' => $diasFaltantes,
                            'aplica' => $aplica,
                            'monto_90_dias' => (float) ($row->monto_90_dias ?? 0),
                            'fecha_inicio_efectiva' => $row->fecha_inicio_efectiva ?? null,
                        ];
                    })
                    ->values();

                $rangoInicio = $filas->pluck('fecha_inicio_efectiva')->filter()->min();
            }
        }

        return [
            'mes' => $mes,
            'sistema' => $sistema,
            'sistemas' => $sistemas,
            'tablaOrigen' => $tabla,
            'filtrosAplicados' => $filtrosAplicados,
            'diasObjetivo' => $diasObjetivo,
            'fechaCorteData' => $fechaCorteData,
            'rangoInicio' => $rangoInicio,
            'rangoFin' => $rangoFin,
            'filas' => $filas,
        ];
    }
}
