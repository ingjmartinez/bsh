<?php

namespace App\Http\Controllers;

use App\Support\InicioVentasCache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InicioController extends Controller
{
    public function index(Request $request)
    {
        [$fechaInicio, $fechaFin, $fechaSeleccionada] = $this->resolverRangoFechas($request);
        $empresaSeleccionada = $this->resolverEmpresa($request);

        $datosCargados = $request->boolean('cargar');

        if ($datosCargados) {
            $ventasInicio = $this->getResumenVentas($fechaInicio, $fechaFin, $empresaSeleccionada);
        } else {
            $ventasInicio = $this->emptyResumenVentas();
            $ventasInicio['balance_mensual'] = $this->buildBalanceMensualVacio($fechaSeleccionada);
        }

        return view('inicio', [
            'fechaInicioVentas' => $fechaInicio->toDateString(),
            'fechaFinVentas' => $fechaFin->toDateString(),
            'fechaSeleccionadaVentas' => $fechaSeleccionada->toDateString(),
            'empresaSeleccionada' => $empresaSeleccionada,
            'empresasFiltro' => $this->getEmpresasFiltro(),
            'ventasInicio' => $ventasInicio,
            'datosCargados' => $datosCargados,
        ]);
    }

    public function ventasData(Request $request)
    {
        [$fechaInicio, $fechaFin, $fechaSeleccionada] = $this->resolverRangoFechas($request);
        $empresaSeleccionada = $this->resolverEmpresa($request);

        return response()->json([
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_fin' => $fechaFin->toDateString(),
            'fecha' => $fechaSeleccionada->toDateString(),
            'empresa' => $empresaSeleccionada,
            'ventas' => $this->getResumenVentas($fechaInicio, $fechaFin, $empresaSeleccionada),
        ]);
    }

    private function resolverEmpresa(Request $request): string
    {
        $empresa = trim((string) $request->query('empresa', 'todos'));

        return $empresa !== '' ? $empresa : 'todos';
    }

    private function resolverRangoFechas(Request $request): array
    {
        $fechaInput = $request->query('fecha');
        $fechaInicioInput = $request->query('fecha_inicio');
        $fechaFinInput = $request->query('fecha_fin');

        $fechaDefault = now()->subDay()->startOfDay();

        if (is_string($fechaInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInput)) {
            $fechaSeleccionada = $this->parseFechaOrDefault($fechaInput, $fechaDefault);

            return [
                $fechaSeleccionada->copy()->startOfDay(),
                $fechaSeleccionada->copy()->endOfDay(),
                $fechaSeleccionada->copy()->startOfDay(),
            ];
        }

        $fechaInicio = $this->parseFechaOrDefault($fechaInicioInput, $fechaDefault);
        $fechaFin = $this->parseFechaOrDefault($fechaFinInput, $fechaDefault);

        if ($fechaInicio->greaterThan($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        return [
            $fechaInicio->startOfDay(),
            $fechaFin->endOfDay(),
            $fechaFin->copy()->startOfDay(),
        ];
    }

    private function parseFechaOrDefault($fecha, Carbon $default): Carbon
    {
        if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $default->copy();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Throwable $e) {
            return $default->copy();
        }
    }

    private function getResumenVentas(Carbon $fechaInicio, Carbon $fechaFin, string $empresa = 'todos'): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $cacheKey = 'inicio_resumen_ventas:v9:' . sha1(
            InicioVentasCache::version() . '|' .
            $fechaInicio->toDateTimeString() . '|' .
            $fechaFin->toDateTimeString() . '|' .
            mb_strtolower($empresa)
        );

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($fechaInicio, $fechaFin, $empresa) {
            try {
                return $this->calcularResumenVentas($fechaInicio, $fechaFin, $empresa);
            } catch (\Throwable $e) {
                Log::error('InicioController: fallo al calcular resumen de ventas', [
                    'fecha_inicio' => $fechaInicio->toDateString(),
                    'fecha_fin' => $fechaFin->toDateString(),
                    'empresa' => $empresa,
                    'error' => $e->getMessage(),
                ]);

                $fallback = $this->emptyResumenVentas();
                $fallback['balance_mensual'] = $this->buildBalanceMensualVacio($fechaFin->copy()->startOfDay());

                return $fallback;
            }
        });
    }

    private function calcularResumenVentas(Carbon $fechaInicio, Carbon $fechaFin, string $empresa = 'todos'): array
    {
        $resumen = $this->emptyResumenVentas();
        $catalogoProductos = $this->getCatalogoProductos();
        $agenciasActivasMap = $this->getAgenciasActivasMap($empresa);
        $resumenLotobet = $this->getResumenVentasPorTabla('vt_usuarios_bet', $fechaInicio, $fechaFin, $catalogoProductos, $agenciasActivasMap);
        $resumenLotonet = $this->getResumenVentasPorTabla('vt_usuarios_net', $fechaInicio, $fechaFin, $catalogoProductos, $agenciasActivasMap);

        $resumen['sistemas']['Lotobet Real'] = $resumenLotobet;
        $resumen['sistemas']['Lotonet'] = $resumenLotonet;

        foreach (['tradicional', 'no_tradicional', 'recargas', 'otros'] as $tipo) {
            $resumen['tipos'][$tipo]['total'] =
                (float) ($resumenLotobet['tipos'][$tipo]['total'] ?? 0) +
                (float) ($resumenLotonet['tipos'][$tipo]['total'] ?? 0);
            $resumen['tipos'][$tipo]['registros'] =
                (int) ($resumenLotobet['tipos'][$tipo]['registros'] ?? 0) +
                (int) ($resumenLotonet['tipos'][$tipo]['registros'] ?? 0);
            $resumen['tipos'][$tipo]['agencias'] =
                (int) ($resumenLotobet['tipos'][$tipo]['agencias'] ?? 0) +
                (int) ($resumenLotonet['tipos'][$tipo]['agencias'] ?? 0);
        }

        $resumen['total_general'] =
            (float) ($resumenLotobet['total_general'] ?? 0) +
            (float) ($resumenLotonet['total_general'] ?? 0);
        $resumen['registros'] =
            (int) ($resumenLotobet['registros'] ?? 0) +
            (int) ($resumenLotonet['registros'] ?? 0);

        $agenciasConVentaIds = collect(array_merge(
            $resumenLotobet['agencias_con_venta_ids'] ?? [],
            $resumenLotonet['agencias_con_venta_ids'] ?? []
        ))->unique()->values()->all();

        $agenciasConVenta = 0;

        foreach ($agenciasConVentaIds as $agenciaId) {
            if (isset($agenciasActivasMap[$agenciaId])) {
                $agenciasConVenta++;
            }
        }

        $totalAgenciasActivas = count($agenciasActivasMap);
        $resumen['agencias_con_venta'] = $agenciasConVenta;
        $resumen['agencias_sin_venta'] = max(0, $totalAgenciasActivas - $agenciasConVenta);
        $resumen['agencias_sin_ventas'] = collect($agenciasActivasMap)
            ->reject(function ($agenciaData, $agenciaId) use ($agenciasConVentaIds) {
                return in_array((string) $agenciaId, $agenciasConVentaIds, true);
            })
            ->map(function ($agenciaData, $agenciaId) {
                $nombreAgencia = trim((string) ($agenciaData['nombre_agencia'] ?? ''));

                if ($nombreAgencia === '') {
                    $nombreAgencia = trim((string) ($agenciaData['agencia'] ?? ''));
                }

                if ($nombreAgencia === '') {
                    $nombreAgencia = trim((string) $agenciaId);
                }

                return [
                    'agencia_id' => trim((string) $agenciaId),
                    'nombre_agencia' => $nombreAgencia,
                    'terminal' => trim((string) ($agenciaData['terminal'] ?? $agenciaId)),
                ];
            })
            ->sortBy('nombre_agencia', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $productosCombinados = [];
        foreach ([$resumenLotobet['productos'] ?? [], $resumenLotonet['productos'] ?? []] as $productosSistema) {
            foreach ($productosSistema as $productoId => $producto) {
                $key = (string) $productoId;

                if (!isset($productosCombinados[$key])) {
                    $productosCombinados[$key] = [
                        'producto_id' => $key,
                        'nombre' => (string) ($producto['nombre'] ?? ('Producto ' . $key)),
                        'tipo' => (string) ($producto['tipo'] ?? 'otros'),
                        'total' => 0,
                        'agencias_ids' => [],
                    ];
                }

                $productosCombinados[$key]['total'] += (float) ($producto['total'] ?? 0);
                foreach (array_keys($producto['agencias_ids'] ?? []) as $agenciaId) {
                    $productosCombinados[$key]['agencias_ids'][(string) $agenciaId] = true;
                }
            }
        }

        $productosNormalizados = collect($productosCombinados)
            ->map(function ($producto) {
                return [
                    'producto_id' => $producto['producto_id'],
                    'nombre' => $producto['nombre'],
                    'tipo' => $producto['tipo'],
                    'total' => (float) ($producto['total'] ?? 0),
                    'agencias' => count($producto['agencias_ids'] ?? []),
                ];
            })
            ->filter(function ($producto) {
                return (float) ($producto['total'] ?? 0) > 0;
            })
            ->values();

        $resumen['productos_no_tradicionales'] = $productosNormalizados
            ->where('tipo', 'no_tradicional')
            ->sortByDesc('total')
            ->take(10)
            ->values()
            ->all();

        $resumen['productos_tradicionales_top'] = $productosNormalizados
            ->where('tipo', 'tradicional')
            ->sortByDesc('total')
            ->take(10)
            ->values()
            ->all();

        try {
            $resumen['balance_mensual'] = $this->getBalanceMensualPorDia(
                $fechaFin->copy()->startOfDay(),
                $empresa,
                $catalogoProductos,
                $agenciasActivasMap
            );
        } catch (\Throwable $e) {
            Log::warning('InicioController: fallo en balance mensual, se aplica fallback', [
                'fecha' => $fechaFin->toDateString(),
                'empresa' => $empresa,
                'error' => $e->getMessage(),
            ]);

            $resumen['balance_mensual'] = $this->buildBalanceMensualVacio($fechaFin->copy()->startOfDay());
        }

        return $resumen;
    }

    private function getBalanceMensualPorDia(Carbon $fechaSeleccionada, string $empresa, array $catalogoProductos, array $agenciasActivasMap): array
    {
        $inicioMes = $fechaSeleccionada->copy()->startOfMonth();
        $finMes = $fechaSeleccionada->copy()->endOfMonth();

        $dias = [];
        $ingresos = [];
        $gastos = [];
        $margen = [];
        $indexPorFecha = [];

        $cursor = $inicioMes->copy();
        while ($cursor->lte($finMes)) {
            $fechaIso = $cursor->toDateString();
            $indexPorFecha[$fechaIso] = count($dias);
            $dias[] = $cursor->format('d');
            $ingresos[] = 0.0;
            $gastos[] = 0.0;
            $margen[] = 0.0;
            $cursor->addDay();
        }

        if (empty($agenciasActivasMap)) {
            return [
                'dias' => $dias,
                'ingresos' => $ingresos,
                'gastos' => $gastos,
                'margen' => $margen,
                'periodo' => [
                    'inicio' => $inicioMes->toDateString(),
                    'fin' => $finMes->toDateString(),
                ],
            ];
        }

        $agenciasActivas = array_keys($agenciasActivasMap);
        $totalesPorFechaTipo = [];

        foreach (['vt_usuarios_bet', 'vt_usuarios_net'] as $tabla) {
            $rows = DB::table($tabla . ' as v')
                ->selectRaw('DATE(v.fecha) AS fecha')
                ->selectRaw('v.producto_id')
                ->selectRaw('SUM(COALESCE(v.monto, 0)) AS total')
                ->whereBetween('v.fecha', [$inicioMes->toDateTimeString(), $finMes->toDateTimeString()])
                ->whereIn(DB::raw('TRIM(CAST(v.agencia_id AS CHAR))'), $agenciasActivas)
                ->groupByRaw('DATE(v.fecha), v.producto_id')
                ->get();

            foreach ($rows as $row) {
                $fecha = (string) ($row->fecha ?? '');
                if ($fecha === '' || !isset($indexPorFecha[$fecha])) {
                    continue;
                }

                $productoId = (string) ($row->producto_id ?? '');
                $tipoBase = (string) ($catalogoProductos[$productoId]['tipo'] ?? 'otros');
                $tipo = $this->normalizeTipo($tipoBase);

                if (!in_array($tipo, ['tradicional', 'no_tradicional', 'recargas'], true)) {
                    continue;
                }

                if (!isset($totalesPorFechaTipo[$fecha])) {
                    $totalesPorFechaTipo[$fecha] = [
                        'tradicional' => 0.0,
                        'no_tradicional' => 0.0,
                        'recargas' => 0.0,
                    ];
                }

                $totalesPorFechaTipo[$fecha][$tipo] += (float) ($row->total ?? 0);
            }
        }

        foreach ($totalesPorFechaTipo as $fecha => $totales) {
            $index = $indexPorFecha[$fecha];
            $ingresos[$index] = round((float) ($totales['tradicional'] ?? 0), 2);
            $gastos[$index] = round((float) ($totales['no_tradicional'] ?? 0), 2);
            $margen[$index] = round((float) ($totales['recargas'] ?? 0), 2);
        }

        return [
            'dias' => $dias,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'margen' => $margen,
            'periodo' => [
                'inicio' => $inicioMes->toDateString(),
                'fin' => $finMes->toDateString(),
            ],
        ];
    }

    private function getResumenVentasPorTabla(string $tabla, Carbon $fechaInicio, Carbon $fechaFin, array $catalogoProductos, array $agenciasActivasMap): array
    {
        $rows = DB::table($tabla . ' as v')
            ->selectRaw("TRIM(CAST(v.agencia_id AS CHAR)) AS agencia_id")
            ->selectRaw('v.producto_id')
            ->selectRaw('MAX(v.tipo) AS tipo')
            ->selectRaw('SUM(COALESCE(v.monto, 0)) AS total')
            ->selectRaw('COUNT(*) AS registros')
            ->whereBetween('v.fecha', [$fechaInicio->toDateTimeString(), $fechaFin->toDateTimeString()])
            ->groupByRaw("TRIM(CAST(v.agencia_id AS CHAR)), v.producto_id")
            ->get();

        $resumen = [
            'total_general' => 0,
            'registros' => 0,
            'agencias_con_venta_ids' => [],
            'productos' => [],
            'tipos' => [
                'tradicional' => ['label' => 'Tradicional', 'total' => 0, 'registros' => 0, 'agencias' => 0],
                'no_tradicional' => ['label' => 'No Tradicional', 'total' => 0, 'registros' => 0, 'agencias' => 0],
                'recargas' => ['label' => 'Recargas', 'total' => 0, 'registros' => 0, 'agencias' => 0],
                'otros' => ['label' => 'Otros', 'total' => 0, 'registros' => 0, 'agencias' => 0],
            ],
        ];

        $agenciasConVentaIds = [];
        $agenciasPorTipo = [
            'tradicional' => [],
            'no_tradicional' => [],
            'recargas' => [],
            'otros' => [],
        ];

        foreach ($rows as $row) {
            $productoId = (string) ($row->producto_id ?? '');
            $catalogo = $catalogoProductos[$productoId] ?? [
                'tipo' => (string) ($row->tipo ?? 'otros'),
                'descripcion' => 'Producto ' . ($productoId !== '' ? $productoId : 'N/D'),
            ];
            $tipoBase = (string) ($catalogo['tipo'] ?? (string) ($row->tipo ?? 'otros'));
            $tipoKey = $this->normalizeTipo($tipoBase);
            $total = (float) ($row->total ?? 0);
            $registros = (int) ($row->registros ?? 0);
            $agencias = 0;
            $agenciaId = trim((string) ($row->agencia_id ?? ''));
            $nombreProducto = trim((string) ($catalogo['descripcion'] ?? ''));

            if ($nombreProducto === '') {
                $nombreProducto = 'Producto ' . ($productoId !== '' ? $productoId : 'N/D');
            }

            if ($agenciaId === '' || !isset($agenciasActivasMap[$agenciaId])) {
                continue;
            }

            $resumen['tipos'][$tipoKey]['total'] += $total;
            $resumen['tipos'][$tipoKey]['registros'] += $registros;
            $resumen['tipos'][$tipoKey]['agencias'] += $agencias;
            $resumen['total_general'] += $total;
            $resumen['registros'] += $registros;

            if ($agenciaId !== '' && $total > 0) {
                $agenciasConVentaIds[$agenciaId] = true;
                $agenciasPorTipo[$tipoKey][$agenciaId] = true;

                $productoKey = $productoId !== '' ? $productoId : 'sin_id';
                if (!isset($resumen['productos'][$productoKey])) {
                    $resumen['productos'][$productoKey] = [
                        'producto_id' => $productoKey,
                        'nombre' => $nombreProducto,
                        'tipo' => $tipoKey,
                        'total' => 0,
                        'agencias_ids' => [],
                    ];
                }

                $resumen['productos'][$productoKey]['total'] += $total;
                $resumen['productos'][$productoKey]['agencias_ids'][$agenciaId] = true;
            }
        }

        $resumen['agencias_con_venta_ids'] = array_keys($agenciasConVentaIds);
        foreach ($agenciasPorTipo as $tipoKey => $agenciasIds) {
            $resumen['tipos'][$tipoKey]['agencias'] = count($agenciasIds);
        }

        return $resumen;
    }

    private function getCatalogoProductos(): array
    {
        return DB::table('catalogo_juegos')
            ->select('producto_id', 'tipo', 'descripcion')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    (string) ($row->producto_id ?? '') => [
                        'tipo' => (string) ($row->tipo ?? ''),
                        'descripcion' => (string) ($row->descripcion ?? ''),
                    ],
                ];
            })
            ->all();
    }

    private function getEmpresasFiltro(): array
    {
        return DB::table('agencias')
            ->whereNotNull('empresa')
            ->whereRaw("TRIM(COALESCE(empresa, '')) <> ''")
            ->selectRaw('DISTINCT TRIM(empresa) AS empresa')
            ->orderBy('empresa')
            ->pluck('empresa')
            ->map(static fn ($empresa) => trim((string) $empresa))
            ->filter(static fn ($empresa) => $empresa !== '')
            ->values()
            ->all();
    }

    private function getAgenciasActivasMap(string $empresa = 'todos'): array
    {
        $query = DB::table('agencias')
            ->where('estatus', 1)
            ->whereNotNull('terminal')
            ->whereRaw("TRIM(CAST(terminal AS CHAR)) <> ''");

        if (mb_strtolower($empresa) !== 'todos') {
            $query->whereRaw("LOWER(TRIM(COALESCE(empresa, ''))) = ?", [mb_strtolower(trim($empresa))]);
        }

        return $query
            ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS agencia_id")
            ->selectRaw("TRIM(COALESCE(nombre_agencia, '')) AS nombre_agencia")
            ->selectRaw("TRIM(COALESCE(agencia, '')) AS agencia")
            ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS terminal")
            ->get()
            ->mapWithKeys(function ($row) {
                $agenciaId = trim((string) ($row->agencia_id ?? ''));

                if ($agenciaId === '') {
                    return [];
                }

                return [
                    $agenciaId => [
                        'agencia_id' => $agenciaId,
                        'nombre_agencia' => trim((string) ($row->nombre_agencia ?? '')),
                        'agencia' => trim((string) ($row->agencia ?? '')),
                        'terminal' => trim((string) ($row->terminal ?? '')),
                    ],
                ];
            })
            ->all();
    }

    private function normalizeTipo(string $tipo): string
    {
        $tipo = strtolower(trim($tipo));

        if ($tipo === 'tradicional') {
            return 'tradicional';
        }

        if ($tipo === 'no tradicional' || $tipo === 'no_tradicional') {
            return 'no_tradicional';
        }

        if ($tipo === 'recarga' || $tipo === 'recargas') {
            return 'recargas';
        }

        return 'otros';
    }

    private function emptyResumenVentas(): array
    {
        $tipos = [
            'tradicional' => ['label' => 'Tradicional', 'total' => 0, 'registros' => 0, 'agencias' => 0],
            'no_tradicional' => ['label' => 'No Tradicional', 'total' => 0, 'registros' => 0, 'agencias' => 0],
            'recargas' => ['label' => 'Recargas', 'total' => 0, 'registros' => 0, 'agencias' => 0],
            'otros' => ['label' => 'Otros', 'total' => 0, 'registros' => 0, 'agencias' => 0],
        ];

        return [
            'total_general' => 0,
            'registros' => 0,
            'agencias_con_venta' => 0,
            'agencias_sin_venta' => 0,
            'agencias_sin_ventas' => [],
            'productos_no_tradicionales' => [],
            'productos_tradicionales_top' => [],
            'balance_mensual' => [
                'dias' => [],
                'ingresos' => [],
                'gastos' => [],
                'margen' => [],
                'periodo' => ['inicio' => null, 'fin' => null],
            ],
            'tipos' => $tipos,
            'sistemas' => [
                'Lotobet Real' => [
                    'total_general' => 0,
                    'registros' => 0,
                    'tipos' => $tipos,
                ],
                'Lotonet' => [
                    'total_general' => 0,
                    'registros' => 0,
                    'tipos' => $tipos,
                ],
            ],
        ];
    }

    private function buildBalanceMensualVacio(Carbon $fechaSeleccionada): array
    {
        $inicioMes = $fechaSeleccionada->copy()->startOfMonth();
        $finMes = $fechaSeleccionada->copy()->endOfMonth();

        $dias = [];
        $ingresos = [];
        $gastos = [];
        $margen = [];

        $cursor = $inicioMes->copy();
        while ($cursor->lte($finMes)) {
            $dias[] = $cursor->format('d');
            $ingresos[] = 0.0;
            $gastos[] = 0.0;
            $margen[] = 0.0;
            $cursor->addDay();
        }

        return [
            'dias' => $dias,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'margen' => $margen,
            'periodo' => [
                'inicio' => $inicioMes->toDateString(),
                'fin' => $finMes->toDateString(),
            ],
        ];
    }
}
