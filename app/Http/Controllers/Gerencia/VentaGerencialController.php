<?php

namespace App\Http\Controllers\Gerencia;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;

class VentaGerencialController extends Controller
{
    public function index(Request $request)
    {
        $debeConsultar = $request->boolean('consultar') || $request->hasAny(['fecha', 'sistema']);
        $fecha = trim((string) $request->query('fecha', now()->format('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $sistema = $this->normalizarSistema($request->query('sistema', 'todos'));

        $resumenAgencias = $debeConsultar ? $this->getResumenVentasAgenciaPorFecha($fecha, $sistema) : [];

        return view('gerencia.venta-gerencial', [
            'debeConsultar' => $debeConsultar,
            'fechaSeleccionada' => $fecha,
            'sistemaSeleccionado' => $sistema,
            'resumenAgencias' => $resumenAgencias,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $fecha = trim((string) $request->query('fecha', now()->format('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $sistema = $this->normalizarSistema($request->query('sistema', 'todos'));

        $rows = collect($this->getResumenVentasAgenciaPorFecha($fecha, $sistema));
        $fileName = sprintf('venta_gerencial_%s_%s.xlsx', str_replace('-', '', $fecha), $sistema);

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize {
            public function __construct(private $rows)
            {
            }

            public function collection()
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return [
                    'Agencia',
                    'Terminal',
                    'Ventas',
                    'Premios Sacados',
                    'Utilidad Bruta',
                ];
            }

            public function map($row): array
            {
                return [
                    (string) ($row['nombre_agencia'] ?? ''),
                    (string) ($row['terminal'] ?? ''),
                    (float) ($row['total_vendido'] ?? 0),
                    (float) ($row['premios_sacados'] ?? 0),
                    (float) ($row['utilidad_bruta'] ?? 0),
                ];
            }
        }, $fileName);
    }

    public function comparativa(Request $request)
    {
        $debeConsultar = $request->hasAny(['fecha', 'sistema', 'agencia', 'terminal', 'tendencia_rango']);

        $fecha = trim((string) $request->query('fecha', now()->format('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $sistema = $this->normalizarSistema($request->query('sistema', 'todos'));
        $tendenciaRango = $this->normalizarTendenciaRango($request->query('tendencia_rango', 'semanal'));

        $terminalBuscada = trim((string) $request->query('terminal', ''));
        $agencia = trim((string) $request->query('agencia', ''));
        if ($agencia === '' && $terminalBuscada !== '') {
            $agencia = $terminalBuscada;
        }

        if ($agencia === '') {
            $agencia = null;
        }

        $resumenComparativo = [];
        $agenciasDisponibles = [];
        $tendenciaSemanal = [
            'labels' => [],
            'series' => [],
        ];

        if ($debeConsultar) {
            $resumenComparativo = $this->getResumenVentasComparativaPorFecha($fecha, $sistema, $agencia);
            $agenciasDisponibles = $this->getAgenciasDisponiblesComparativa($fecha, $sistema);
            $tendenciaSemanal = $this->getTendenciaSemanalComparativa($fecha, $sistema, $agencia, $tendenciaRango);
        }

        return view('gerencia.venta-comparativa', [
            'fechaSeleccionada' => $fecha,
            'sistemaSeleccionado' => $sistema,
            'agenciaSeleccionada' => $agencia,
            'terminalBuscada' => $terminalBuscada,
            'tendenciaRango' => $tendenciaRango,
            'tendenciaRangoLabel' => $this->getTendenciaRangoLabel($tendenciaRango),
            'agenciasDisponibles' => $agenciasDisponibles,
            'resumenComparativo' => $resumenComparativo,
            'tendenciaSemanal' => $tendenciaSemanal,
        ]);
    }

    public function exportExcelComparativa(Request $request)
    {
        $fecha = trim((string) $request->query('fecha', now()->format('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $sistema = $this->normalizarSistema($request->query('sistema', 'todos'));

        $terminalBuscada = trim((string) $request->query('terminal', ''));
        $agencia = trim((string) $request->query('agencia', ''));
        if ($agencia === '' && $terminalBuscada !== '') {
            $agencia = $terminalBuscada;
        }

        if ($agencia === '') {
            $agencia = null;
        }

        $rows = collect($this->getResumenVentasComparativaPorFecha($fecha, $sistema, $agencia));
        $fileName = sprintf('venta_comparativa_%s_%s.xlsx', str_replace('-', '', $fecha), $sistema);

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize {
            public function __construct(private $rows)
            {
            }

            public function collection()
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return [
                    'Agencia',
                    'Terminal',
                    'Ventas Hoy',
                    'Ventas Ayer',
                    'Ventas Ultimos 2 Dias',
                    'Ventas Ultimos 3 Dias',
                ];
            }

            public function map($row): array
            {
                return [
                    (string) ($row['nombre_agencia'] ?? ''),
                    (string) ($row['terminal'] ?? ''),
                    (float) ($row['ventas_hoy'] ?? 0),
                    (float) ($row['ventas_ayer'] ?? 0),
                    (float) ($row['ventas_ultimos_2_dias'] ?? 0),
                    (float) ($row['ventas_ultimos_3_dias'] ?? 0),
                ];
            }
        }, $fileName);
    }

    private function getResumenVentasAgenciaPorFecha(string $fecha, string $sistema): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $inicioDia = Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay()->toDateTimeString();
        $finDia = Carbon::createFromFormat('Y-m-d', $fecha)->addDay()->startOfDay()->toDateTimeString();
        $mapasTablas = $this->getMapasTablasPorSistema($sistema);
        $ventasByAgencia = [];
        $premiosByAgencia = [];

        foreach ($mapasTablas as $mapaTablas) {
            $ventasRows = DB::table($mapaTablas['ventas'] . ' as v')
                ->select('v.agencia_id as agencia')
                ->selectRaw('SUM(COALESCE(monto, 0)) AS total_vendido')
                ->whereNotNull('v.agencia_id')
                ->where('v.fecha', '>=', $inicioDia)
                ->where('v.fecha', '<', $finDia)
                ->groupBy('v.agencia_id')
                ->get();

            foreach ($ventasRows as $row) {
                $agencia = $this->normalizarAgenciaId($row->agencia ?? '');
                if ($agencia === '') {
                    continue;
                }

                $ventasByAgencia[$agencia] = (float) ($ventasByAgencia[$agencia] ?? 0) + (float) ($row->total_vendido ?? 0);
            }

            $premiosRows = DB::table($mapaTablas['premios'])
                ->select('agencia_id as agencia')
                ->selectRaw('SUM(COALESCE(monto, 0)) AS premios_pagados')
                ->where('fecha', '>=', $inicioDia)
                ->where('fecha', '<', $finDia)
                ->groupBy('agencia_id')
                ->get();

            foreach ($premiosRows as $row) {
                $agencia = $this->normalizarAgenciaId($row->agencia ?? '');
                if ($agencia === '') {
                    continue;
                }

                $premiosByAgencia[$agencia] = (float) ($premiosByAgencia[$agencia] ?? 0) + (float) ($row->premios_pagados ?? 0);
            }
        }

        $nombresAgenciaByTerminal = $this->getNombresAgenciaByTerminal();

        return collect($ventasByAgencia)
            ->map(function ($totalVendido, $agencia) use ($premiosByAgencia, $nombresAgenciaByTerminal) {
            $agencia = (string) ($agencia ?: 'SIN AGENCIA');
            $totalVendido = (float) ($totalVendido ?? 0);
            $premiosSacados = (float) ($premiosByAgencia[$agencia] ?? 0);
            $nombreAgencia = trim((string) ($nombresAgenciaByTerminal[$agencia] ?? ''));
            $utilidadBruta = $totalVendido - $premiosSacados;

            return [
                'agencia' => $agencia,
                'terminal' => $agencia,
                'nombre_agencia' => $nombreAgencia !== '' ? $nombreAgencia : $agencia,
                'total_vendido' => $totalVendido,
                'premios_sacados' => $premiosSacados,
                'utilidad_bruta' => $utilidadBruta,
            ];
        })
            ->sortByDesc('total_vendido')
            ->values()
            ->toArray();
    }

    private function normalizarSistema(mixed $sistema): string
    {
        $valor = strtolower(trim((string) $sistema));

        if (!in_array($valor, ['todos', 'lotobet', 'lotonet'], true)) {
            return 'todos';
        }

        return $valor;
    }

    private function normalizarTendenciaRango(mixed $rango): string
    {
        $valor = strtolower(trim((string) $rango));

        if (!in_array($valor, ['semanal', 'quincenal', 'mensual'], true)) {
            return 'semanal';
        }

        return $valor;
    }

    private function getTendenciaRangoLabel(string $rango): string
    {
        return match ($rango) {
            'quincenal' => 'Quincenal',
            'mensual' => 'Un mes',
            default => 'Semanal',
        };
    }

    private function getTendenciaRangoDias(string $rango): int
    {
        return match ($rango) {
            'quincenal' => 15,
            'mensual' => 30,
            default => 7,
        };
    }

    private function getMapasTablasPorSistema(string $sistema): array
    {
        if ($sistema === 'todos') {
            return [
                [
                    'ventas' => 'vt_usuarios_bet',
                    'premios' => 'premios_bet',
                ],
                [
                    'ventas' => 'vt_usuarios_net',
                    'premios' => 'premios_net',
                ],
            ];
        }

        if ($sistema === 'lotonet') {
            return [[
                'ventas' => 'vt_usuarios_net',
                'premios' => 'premios_net',
            ]];
        }

        return [[
            'ventas' => 'vt_usuarios_bet',
            'premios' => 'premios_bet',
        ]];
    }

    private function getResumenVentasComparativaPorFecha(string $fecha, string $sistema, ?string $agenciaFiltro = null): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $fechaBase = Carbon::createFromFormat('Y-m-d', $fecha);
        $inicioHoy = $fechaBase->copy()->startOfDay();
        $finHoy = $inicioHoy->copy()->addDay();
        $inicioAyer = $inicioHoy->copy()->subDay();
        $finAyer = $inicioHoy->copy();
        $inicioDosDias = $inicioHoy->copy()->subDays(2);
        $finDosDias = $inicioHoy->copy()->subDay();
        $inicioTresDias = $inicioHoy->copy()->subDays(3);
        $finTresDias = $inicioHoy->copy()->subDays(2);

        $inicioVentana = $inicioTresDias->toDateTimeString();
        $finVentana = $finHoy->toDateTimeString();
        $filtroAgencia = $agenciaFiltro !== null ? $this->normalizarAgenciaId($agenciaFiltro) : null;

        $mapasTablas = $this->getMapasTablasPorSistema($sistema);
        $totalesPorAgencia = [];
        $nombresAgenciaByTerminal = $this->getNombresAgenciaByTerminal();

        foreach ($mapasTablas as $mapaTablas) {
            $rows = DB::table($mapaTablas['ventas'] . ' as v')
                ->select('v.agencia_id as agencia')
                ->selectRaw('SUM(CASE WHEN v.fecha >= ? AND v.fecha < ? THEN COALESCE(v.monto, 0) ELSE 0 END) AS ventas_hoy', [$inicioHoy->toDateTimeString(), $finHoy->toDateTimeString()])
                ->selectRaw('SUM(CASE WHEN v.fecha >= ? AND v.fecha < ? THEN COALESCE(v.monto, 0) ELSE 0 END) AS ventas_ayer', [$inicioAyer->toDateTimeString(), $finAyer->toDateTimeString()])
                ->selectRaw('SUM(CASE WHEN v.fecha >= ? AND v.fecha < ? THEN COALESCE(v.monto, 0) ELSE 0 END) AS ventas_hace_2_dias', [$inicioDosDias->toDateTimeString(), $finDosDias->toDateTimeString()])
                ->selectRaw('SUM(CASE WHEN v.fecha >= ? AND v.fecha < ? THEN COALESCE(v.monto, 0) ELSE 0 END) AS ventas_hace_3_dias', [$inicioTresDias->toDateTimeString(), $finTresDias->toDateTimeString()])
                ->selectRaw('SUM(CASE WHEN v.fecha >= ? AND v.fecha < ? THEN COALESCE(v.monto, 0) ELSE 0 END) AS ventas_ultimos_2_dias', [$inicioAyer->toDateTimeString(), $finHoy->toDateTimeString()])
                ->selectRaw('SUM(CASE WHEN v.fecha >= ? AND v.fecha < ? THEN COALESCE(v.monto, 0) ELSE 0 END) AS ventas_ultimos_3_dias', [$inicioDosDias->toDateTimeString(), $finHoy->toDateTimeString()])
                ->whereNotNull('v.agencia_id')
                ->when($filtroAgencia !== null, function ($query) use ($filtroAgencia) {
                    $query->where('v.agencia_id', $filtroAgencia);
                })
                ->where('v.fecha', '>=', $inicioVentana)
                ->where('v.fecha', '<', $finVentana)
                ->groupBy('v.agencia_id')
                ->get();

            foreach ($rows as $row) {
                $agencia = $this->normalizarAgenciaId($row->agencia ?? '');
                if ($agencia === '') {
                    continue;
                }

                if (!isset($totalesPorAgencia[$agencia])) {
                    $totalesPorAgencia[$agencia] = [
                        'agencia' => $agencia,
                        'terminal' => $agencia,
                        'nombre_agencia' => trim((string) ($nombresAgenciaByTerminal[$agencia] ?? '')),
                        'ventas_hoy' => 0.0,
                        'ventas_ayer' => 0.0,
                        'ventas_hace_2_dias' => 0.0,
                        'ventas_hace_3_dias' => 0.0,
                        'ventas_ultimos_2_dias' => 0.0,
                        'ventas_ultimos_3_dias' => 0.0,
                    ];
                }

                if ($totalesPorAgencia[$agencia]['nombre_agencia'] === '') {
                    $totalesPorAgencia[$agencia]['nombre_agencia'] = $agencia;
                }

                $totalesPorAgencia[$agencia]['ventas_hoy'] += (float) ($row->ventas_hoy ?? 0);
                $totalesPorAgencia[$agencia]['ventas_ayer'] += (float) ($row->ventas_ayer ?? 0);
                $totalesPorAgencia[$agencia]['ventas_hace_2_dias'] += (float) ($row->ventas_hace_2_dias ?? 0);
                $totalesPorAgencia[$agencia]['ventas_hace_3_dias'] += (float) ($row->ventas_hace_3_dias ?? 0);
                $totalesPorAgencia[$agencia]['ventas_ultimos_2_dias'] += (float) ($row->ventas_ultimos_2_dias ?? 0);
                $totalesPorAgencia[$agencia]['ventas_ultimos_3_dias'] += (float) ($row->ventas_ultimos_3_dias ?? 0);
            }
        }

        return collect($totalesPorAgencia)
            ->map(function ($row) {
                $agencia = (string) ($row['agencia'] ?? 'SIN AGENCIA');
                $nombreAgencia = trim((string) ($row['nombre_agencia'] ?? ''));
                $row['nombre_agencia'] = $nombreAgencia !== '' ? $nombreAgencia : $agencia;

                return $row;
            })
            ->sortByDesc('ventas_hoy')
            ->values()
            ->toArray();
    }

    private function getAgenciasDisponiblesComparativa(string $fecha, string $sistema): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $fechaInicio = Carbon::createFromFormat('Y-m-d', $fecha)->subDays(6)->startOfDay()->toDateTimeString();
        $fechaFin = Carbon::createFromFormat('Y-m-d', $fecha)->addDay()->startOfDay()->toDateTimeString();
        $mapasTablas = $this->getMapasTablasPorSistema($sistema);
        $agencias = [];
        $nombresAgenciaByTerminal = $this->getNombresAgenciaByTerminal();

        foreach ($mapasTablas as $mapaTablas) {
            $rows = DB::table($mapaTablas['ventas'] . ' as v')
                ->select('v.agencia_id as agencia')
                ->whereNotNull('v.agencia_id')
                ->where('v.fecha', '>=', $fechaInicio)
                ->where('v.fecha', '<', $fechaFin)
                ->groupBy('v.agencia_id')
                ->get();

            foreach ($rows as $row) {
                $agencia = $this->normalizarAgenciaId($row->agencia ?? '');
                if ($agencia === '') {
                    continue;
                }

                $nombre = trim((string) ($nombresAgenciaByTerminal[$agencia] ?? ''));
                if (!isset($agencias[$agencia])) {
                    $agencias[$agencia] = [
                        'agencia' => $agencia,
                        'nombre' => $nombre !== '' ? $nombre : $agencia,
                    ];
                } elseif ($agencias[$agencia]['nombre'] === $agencia && $nombre !== '') {
                    $agencias[$agencia]['nombre'] = $nombre;
                }
            }
        }

        return collect($agencias)
            ->sortBy('agencia')
            ->values()
            ->toArray();
    }

    private function getTendenciaSemanalComparativa(string $fecha, string $sistema, ?string $agenciaFiltro = null, string $rango = 'semanal'): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $dias = $this->getTendenciaRangoDias($this->normalizarTendenciaRango($rango));
        $fechaInicio = Carbon::createFromFormat('Y-m-d', $fecha)->subDays($dias - 1)->startOfDay();
        $fechaFin = Carbon::createFromFormat('Y-m-d', $fecha)->addDay()->startOfDay();
        $filtroAgencia = $agenciaFiltro !== null ? $this->normalizarAgenciaId($agenciaFiltro) : null;
        $mapasTablas = $this->getMapasTablasPorSistema($sistema);
        $totalesPorFecha = [];

        foreach ($mapasTablas as $mapaTablas) {
            $rows = DB::table($mapaTablas['ventas'] . ' as v')
                ->selectRaw('DATE(v.fecha) AS fecha')
                ->selectRaw('SUM(COALESCE(v.monto, 0)) AS total_ventas')
                ->whereNotNull('v.agencia_id')
                ->when($filtroAgencia !== null, function ($query) use ($filtroAgencia) {
                    $query->where('v.agencia_id', $filtroAgencia);
                })
                ->where('v.fecha', '>=', $fechaInicio->toDateTimeString())
                ->where('v.fecha', '<', $fechaFin->toDateTimeString())
                ->groupBy(DB::raw('DATE(v.fecha)'))
                ->orderBy(DB::raw('DATE(v.fecha)'))
                ->get();

            foreach ($rows as $row) {
                $fechaRow = (string) ($row->fecha ?? '');
                if ($fechaRow === '') {
                    continue;
                }

                $totalesPorFecha[$fechaRow] = (float) ($totalesPorFecha[$fechaRow] ?? 0) + (float) ($row->total_ventas ?? 0);
            }
        }

        $labels = [];
        $series = [];

        $cursor = $fechaInicio->copy();
        $fin = Carbon::createFromFormat('Y-m-d', $fecha);

        while ($cursor->lessThanOrEqualTo($fin)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('d/m');
            $series[] = (float) ($totalesPorFecha[$key] ?? 0);
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    private function normalizarAgenciaId(mixed $agencia): string
    {
        return trim((string) $agencia);
    }

    private function getNombresAgenciaByTerminal(): array
    {
        static $cache = null;

        if (is_array($cache)) {
            return $cache;
        }

        $cache = DB::table('agencias')
            ->select('terminal', 'nombre_agencia')
            ->whereNotNull('terminal')
            ->get()
            ->mapWithKeys(function ($row) {
                $terminal = $this->normalizarAgenciaId($row->terminal ?? '');
                $nombre = trim((string) ($row->nombre_agencia ?? ''));

                return [$terminal => $nombre];
            })
            ->toArray();

        return $cache;
    }
}
