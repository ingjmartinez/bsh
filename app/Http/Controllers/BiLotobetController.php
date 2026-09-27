<?php

namespace App\Http\Controllers;

use App\Http\Requests\BiLotobetDashboardRequest;
use App\Http\Requests\BiLotobetMonthlyRequest;
use App\Http\Requests\BiLotobetProductosRequest;
use App\Http\Requests\BiLotobetRazaRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiLotobetController extends Controller
{
    public function index(): View
    {
        return view('bi.lotobet-real-index');
    }

    public function dashboard(): View
    {
        $ultimaFecha = DB::table('ventas_usuarios_bet')->max('fecha') ?? now()->toDateString();

        return view('bi.lotobet-real', [
            'ultimaFecha' => CarbonImmutable::parse($ultimaFecha)->toDateString(),
            'filtros' => [
                'grupos' => $this->opcionesAgencia('grupo'),
                'centrales' => $this->opcionesAgencia('central'),
                'gerentes' => $this->opcionesAgencia('gerente_de_servicio'),
                'tiposPago' => $this->opcionesAgencia('tipo_pago'),
            ],
        ]);
    }

    public function monthly(): View
    {
        return view('bi.lotobet-real-mensual', [
            'filtros' => [
                'grupos' => $this->opcionesAgencia('grupo'),
                'centrales' => $this->opcionesAgencia('central'),
                'gerentes' => $this->opcionesAgencia('gerente_de_servicio'),
                'terminales' => $this->opcionesAgencia('terminal'),
            ],
        ]);
    }

    public function monthlyData(BiLotobetMonthlyRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $ultimaFecha = CarbonImmutable::parse(DB::table('ventas_usuarios_bet')->max('fecha') ?? now());
        $primerMes = $ultimaFecha->startOfMonth()->subMonths(11);

        $ventasLotobet = $this->ventasQuery($filters, $primerMes, $ultimaFecha)
            ->selectRaw("DATE_FORMAT(v.fecha, '%Y-%m') AS mes, SUM(v.monto) AS total")
            ->groupByRaw("DATE_FORMAT(v.fecha, '%Y-%m')")
            ->pluck('total', 'mes');
        $ventasRaza = $this->ventasRazaQuery($filters, $primerMes, $ultimaFecha)
            ->selectRaw("DATE_FORMAT(d.fecha, '%Y-%m') AS mes, SUM(d.ventas) AS total")
            ->groupByRaw("DATE_FORMAT(d.fecha, '%Y-%m')")
            ->pluck('total', 'mes');

        $meses = collect(range(0, 11))->map(function (int $offset) use ($primerMes, $ultimaFecha, $ventasLotobet, $ventasRaza): array {
            $fecha = $primerMes->addMonths($offset);
            $key = $fecha->format('Y-m');
            $total = (float) ($ventasLotobet[$key] ?? 0) + (float) ($ventasRaza[$key] ?? 0);
            $dias = $fecha->isSameMonth($ultimaFecha) ? $ultimaFecha->day : $fecha->daysInMonth;

            return [
                'mes' => $key,
                'etiqueta' => ucfirst($fecha->locale('es')->translatedFormat('F Y')),
                'total' => $total,
                'promedio' => $dias > 0 ? $total / $dias : 0,
            ];
        });

        return response()->json(['meses' => $meses]);
    }

    public function raza(): View
    {
        $ultimaFecha = DB::table('ventas_ds_virtual')->max('fecha') ?? now()->toDateString();

        return view('bi.lotobet-real-raza', [
            'ultimaFecha' => CarbonImmutable::parse($ultimaFecha)->toDateString(),
            'filtros' => [
                'grupos' => $this->opcionesAgencia('grupo'),
                'centrales' => $this->opcionesAgencia('central'),
                'gerentes' => $this->opcionesAgencia('gerente_de_servicio'),
                'terminales' => $this->opcionesAgencia('terminal'),
            ],
        ]);
    }

    public function razaData(BiLotobetRazaRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $desde = CarbonImmutable::parse($filters['fecha_desde']);
        $hasta = CarbonImmutable::parse($filters['fecha_hasta']);
        $actual = $this->ventasRazaQuery($filters, $desde, $hasta);
        $anterior = $this->ventasRazaQuery($filters, $desde->subWeek(), $hasta->subWeek());

        $ventas = (float) (clone $actual)->sum('d.ventas');
        $premios = (float) (clone $actual)->sum('d.premios');
        $ventasAnteriores = (float) (clone $anterior)->sum('d.ventas');
        $enlazadas = (clone $actual)->whereNotNull('a.id')->distinct()->count('d.agencia_id');
        $vendieronRaza = (clone $actual)->where('d.ventas', '>', 0)->distinct()->count('d.agencia_id');
        $vendieron = $this->ventasQuery($filters, $desde, $hasta)
            ->where('v.monto', '>', 0)->distinct()->count('v.agencia_id');
        $activas = $this->agenciasQuery($filters)->where('a.estatus', 1)->count();

        $graficoDesde = $hasta->subDays(6);
        $porDia = $this->ventasRazaQuery($filters, $graficoDesde, $hasta)
            ->selectRaw('DATE(d.fecha) AS fecha, SUM(d.ventas) AS total')
            ->groupByRaw('DATE(d.fecha)')->pluck('total', 'fecha');
        $grafico = collect(range(0, 6))->map(function (int $offset) use ($graficoDesde, $porDia): array {
            $fecha = $graficoDesde->addDays($offset);

            return [
                'dia' => ucfirst($fecha->locale('es')->dayName),
                'total' => (float) ($porDia[$fecha->toDateString()] ?? 0),
            ];
        });

        $resultado = $ventas - $premios;

        return response()->json([
            'virtuales' => [
                'activas' => $activas,
                'vendieron' => $vendieron,
                'enlazadas' => $enlazadas,
                'vendieron_raza' => $vendieronRaza,
            ],
            'ventas' => [
                'total' => $ventas,
                'promedio_enlazada' => $enlazadas > 0 ? $ventas / $enlazadas : 0,
                'variacion' => $this->variacion($ventas, $ventasAnteriores),
            ],
            'premios' => [
                'total' => $premios,
                'promedio_enlazada' => $enlazadas > 0 ? $premios / $enlazadas : 0,
            ],
            'resultado' => [
                'total' => $resultado,
                'promedio_enlazada' => $enlazadas > 0 ? $resultado / $enlazadas : 0,
            ],
            'jackpot' => 0,
            'grafico' => $grafico,
        ]);
    }

    public function productos(): View
    {
        $ultimaFecha = DB::table('ventas_usuarios_bet')->max('fecha') ?? now()->toDateString();

        return view('bi.lotobet-real-productos', [
            'ultimaFecha' => CarbonImmutable::parse($ultimaFecha)->toDateString(),
            'filtros' => [
                'grupos' => $this->opcionesAgencia('grupo'), 'centrales' => $this->opcionesAgencia('central'),
                'gerentes' => $this->opcionesAgencia('gerente_de_servicio'), 'terminales' => $this->opcionesAgencia('terminal'),
            ],
        ]);
    }

    public function productosData(BiLotobetProductosRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $desde = CarbonImmutable::parse($filters['fecha_desde']);
        $hasta = CarbonImmutable::parse($filters['fecha_hasta']);
        $tipo = $filters['categoria'] === 'tradicional' ? 'Tradicional' : 'No Tradicional';

        $build = function (CarbonImmutable $inicio, CarbonImmutable $fin) use ($filters, $tipo): Builder {
            return $this->ventasQuery($filters, $inicio, $fin)
                ->leftJoin('catalogo_juegos as c', 'c.producto_id', '=', 'v.producto_id')
                ->whereRaw("LOWER(COALESCE(NULLIF(TRIM(c.tipo), ''), NULLIF(TRIM(v.tipo), ''))) = ?", [strtolower($tipo)]);
        };

        $ventas = (float) $build($desde, $hasta)->sum('v.monto');
        $terminalesActivas = $this->agenciasQuery($filters)->where('a.estatus', 1)->count();
        $terminalesVendieron = $build($desde, $hasta)
            ->where('a.estatus', 1)
            ->where('v.monto', '>', 0)
            ->distinct()
            ->count('v.agencia_id');
        $mesInicio = $hasta->startOfMonth();
        $ventaMes = (float) $build($mesInicio, $hasta)->sum('v.monto');
        $mesAnteriorInicio = $mesInicio->subMonth();
        $diaComparableAnterior = min($hasta->day, $mesAnteriorInicio->daysInMonth);
        $mesAnteriorFin = $mesAnteriorInicio->addDays($diaComparableAnterior - 1);
        $ventaMesAnterior = (float) $build($mesAnteriorInicio, $mesAnteriorFin)->sum('v.monto');

        $graficoInicio = $hasta->subDays(6);
        $diarias = $build($graficoInicio->subDay(), $hasta)->selectRaw('DATE(v.fecha) AS fecha, SUM(v.monto) AS total')
            ->groupByRaw('DATE(v.fecha)')->pluck('total', 'fecha');
        $grafico = collect(range(0, 6))->map(function (int $offset) use ($graficoInicio, $diarias): array {
            $fecha = $graficoInicio->addDays($offset);
            $actual = (float) ($diarias[$fecha->toDateString()] ?? 0);
            $anterior = (float) ($diarias[$fecha->subDay()->toDateString()] ?? 0);

            return ['dia' => ucfirst($fecha->locale('es')->dayName), 'total' => $actual, 'variacion' => $this->variacion($actual, $anterior) ?? 0];
        });
        $productos = $build($desde, $hasta)
            ->selectRaw("COALESCE(NULLIF(TRIM(c.descripcion), ''), NULLIF(TRIM(v.descripcion), ''), CONCAT('Producto ', v.producto_id)) AS producto, SUM(v.monto) AS total")
            ->groupByRaw("COALESCE(NULLIF(TRIM(c.descripcion), ''), NULLIF(TRIM(v.descripcion), ''), CONCAT('Producto ', v.producto_id))")
            ->orderByDesc('total')->limit(10)->get();

        return response()->json([
            'terminales' => ['activas' => $terminalesActivas, 'vendieron' => $terminalesVendieron, 'sin_ventas' => max(0, $terminalesActivas - $terminalesVendieron)],
            'ventas' => $ventas, 'variacion_mes' => $this->variacion($ventaMes, $ventaMesAnterior),
            'promedio_dia' => $ventaMes / max(1, $hasta->day),
            'grafico' => $grafico, 'productos' => $productos,
        ]);
    }

    public function data(BiLotobetDashboardRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $desde = CarbonImmutable::parse($filters['fecha_desde']);
        $hasta = CarbonImmutable::parse($filters['fecha_hasta']);
        $comparacionDesde = $desde->subWeek();
        $comparacionHasta = $hasta->subWeek();

        $terminales = $this->agenciasQuery($filters);
        $totalTerminales = (clone $terminales)->count();
        $activas = (clone $terminales)->where('estatus', 1)->count();
        $inactivas = max(0, $totalTerminales - $activas);

        $ventasActuales = $this->ventasQuery($filters, $desde, $hasta);
        $ventasRazaActuales = $this->ventasRazaQuery($filters, $desde, $hasta);
        $terminalesLotobet = (clone $ventasActuales)
            ->whereNotNull('a.id')
            ->where('v.monto', '>', 0)
            ->selectRaw('DISTINCT TRIM(v.agencia_id) AS terminal');
        $terminalesRaza = (clone $ventasRazaActuales)
            ->whereNotNull('a.id')
            ->where('d.ventas', '>', 0)
            ->selectRaw('DISTINCT TRIM(d.agencia_id) AS terminal');
        $vendieron = DB::query()->fromSub($terminalesLotobet->union($terminalesRaza), 'terminales')->count();

        $ventaRaza = (float) (clone $ventasRazaActuales)->sum('d.ventas');
        $ventaRazaPrevia = (float) $this->ventasRazaQuery($filters, $comparacionDesde, $comparacionHasta)->sum('d.ventas');
        $ventaGlobal = (float) (clone $ventasActuales)->sum('v.monto') + $ventaRaza;
        $ventasPrevias = (float) $this->ventasQuery($filters, $comparacionDesde, $comparacionHasta)->sum('v.monto') + $ventaRazaPrevia;
        $premios = (float) $this->premiosQuery($filters, $desde, $hasta)->sum('p.monto');
        $premiosPrevios = (float) $this->premiosQuery($filters, $comparacionDesde, $comparacionHasta)->sum('p.monto');

        $bucketExpression = $this->bucketExpression();
        $porCategoria = $this->ventasQuery($filters, $desde, $hasta)
            ->leftJoin('catalogo_juegos as c', 'c.producto_id', '=', 'v.producto_id')
            ->selectRaw("{$bucketExpression} AS categoria, SUM(v.monto) AS total")
            ->groupByRaw($bucketExpression)
            ->pluck('total', 'categoria');
        $categoriasPrevias = $this->ventasQuery($filters, $comparacionDesde, $comparacionHasta)
            ->leftJoin('catalogo_juegos as c', 'c.producto_id', '=', 'v.producto_id')
            ->selectRaw("{$bucketExpression} AS categoria, SUM(v.monto) AS total")
            ->groupByRaw($bucketExpression)
            ->pluck('total', 'categoria');
        $porCategoria['raza'] = $ventaRaza;
        $categoriasPrevias['raza'] = $ventaRazaPrevia;

        $graficoDesde = $hasta->subDays(6);
        $ventasDiarias = $this->ventasQuery($filters, $graficoDesde, $hasta)
            ->selectRaw('DATE(v.fecha) AS fecha, SUM(v.monto) AS total')
            ->groupByRaw('DATE(v.fecha)')
            ->pluck('total', 'fecha');
        $ventasRazaDiarias = $this->ventasRazaQuery($filters, $graficoDesde, $hasta)
            ->selectRaw('DATE(d.fecha) AS fecha, SUM(d.ventas) AS total')
            ->groupByRaw('DATE(d.fecha)')
            ->pluck('total', 'fecha');
        $grafico = collect(range(0, 6))->map(function (int $offset) use ($graficoDesde, $ventasDiarias, $ventasRazaDiarias): array {
            $fecha = $graficoDesde->addDays($offset);
            $fechaKey = $fecha->toDateString();

            return [
                'fecha' => $fechaKey,
                'dia' => ucfirst($fecha->locale('es')->dayName),
                'total' => (float) ($ventasDiarias[$fechaKey] ?? 0) + (float) ($ventasRazaDiarias[$fechaKey] ?? 0),
            ];
        })->values();

        $categorias = collect(['tradicional', 'no_tradicional', 'recargas', 'raza', 'paqueticos'])
            ->mapWithKeys(function (string $categoria) use ($porCategoria, $categoriasPrevias, $totalTerminales): array {
                $actual = (float) ($porCategoria[$categoria] ?? 0);
                $anterior = (float) ($categoriasPrevias[$categoria] ?? 0);

                return [$categoria => [
                    'total' => $actual,
                    'promedio_terminal' => $totalTerminales > 0 ? $actual / $totalTerminales : 0,
                    'variacion' => $this->variacion($actual, $anterior),
                ]];
            });

        return response()->json([
            'terminales' => [
                'total' => $totalTerminales,
                'activas' => $activas,
                'inactivas' => $inactivas,
                'vendieron' => $vendieron,
                'sin_ventas' => max(0, $totalTerminales - $vendieron),
            ],
            'venta_global' => [
                'total' => $ventaGlobal,
                'promedio_terminal' => $totalTerminales > 0 ? $ventaGlobal / $totalTerminales : 0,
                'variacion' => $this->variacion($ventaGlobal, $ventasPrevias),
            ],
            'premios' => [
                'total' => $premios,
                'promedio_terminal' => $totalTerminales > 0 ? $premios / $totalTerminales : 0,
                'variacion' => $this->variacion($premios, $premiosPrevios),
                'con_datos' => $premios > 0,
            ],
            'categorias' => $categorias,
            'resultado_bruto' => [
                'total' => $ventaGlobal - $premios,
                'promedio_terminal' => $totalTerminales > 0 ? ($ventaGlobal - $premios) / $totalTerminales : 0,
                'variacion' => $this->variacion($ventaGlobal - $premios, $ventasPrevias - $premiosPrevios),
            ],
            'grafico' => $grafico,
        ]);
    }

    /** @return array<int, string> */
    private function opcionesAgencia(string $column): array
    {
        return DB::table('agencias')->whereNull('deleted_at')->whereNotNull($column)
            ->whereRaw("TRIM({$column}) <> ''")->distinct()->orderBy($column)->pluck($column)->all();
    }

    /** @param array<string, mixed> $filters */
    private function agenciasQuery(array $filters): Builder
    {
        return $this->applyAgencyFilters(
            DB::table('agencias as a')->whereNull('a.deleted_at'),
            $filters
        );
    }

    /** @param array<string, mixed> $filters */
    private function ventasQuery(array $filters, CarbonImmutable $desde, CarbonImmutable $hasta): Builder
    {
        $query = DB::table('ventas_usuarios_bet as v')
            ->leftJoin('agencias as a', DB::raw('TRIM(a.terminal)'), '=', DB::raw('TRIM(v.agencia_id)'))
            ->whereBetween('v.fecha', [$desde->toDateString(), $hasta->toDateString()]);

        return $this->applyAgencyFilters($query, $filters);
    }

    /** @param array<string, mixed> $filters */
    private function premiosQuery(array $filters, CarbonImmutable $desde, CarbonImmutable $hasta): Builder
    {
        $query = DB::table('premios_bet as p')
            ->leftJoin('agencias as a', DB::raw('TRIM(a.terminal)'), '=', DB::raw('TRIM(p.agencia_id)'))
            ->whereBetween('p.fecha', [$desde->toDateString(), $hasta->toDateString()]);

        return $this->applyAgencyFilters($query, $filters);
    }

    /** @param array<string, mixed> $filters */
    private function ventasRazaQuery(array $filters, CarbonImmutable $desde, CarbonImmutable $hasta): Builder
    {
        $query = DB::table('ventas_ds_virtual as d')
            ->leftJoin('agencias as a', DB::raw('TRIM(a.terminal)'), '=', DB::raw('TRIM(d.agencia_id)'))
            ->whereBetween('d.fecha', [$desde->toDateString(), $hasta->toDateString()]);

        return $this->applyAgencyFilters($query, $filters);
    }

    /** @param array<string, mixed> $filters */
    private function applyAgencyFilters(Builder $query, array $filters): Builder
    {
        foreach (['grupo' => 'grupo', 'central' => 'central', 'gerente' => 'gerente_de_servicio', 'tipo_pago' => 'tipo_pago', 'terminal' => 'terminal'] as $key => $column) {
            if (! empty($filters[$key])) {
                $query->where("a.{$column}", $filters[$key]);
            }
        }

        return $query;
    }

    private function bucketExpression(): string
    {
        return "CASE
            WHEN LOWER(COALESCE(c.descripcion, v.descripcion, '')) LIKE '%raza%' THEN 'raza'
            WHEN LOWER(COALESCE(c.descripcion, v.descripcion, '')) LIKE '%paquet%' THEN 'paqueticos'
            WHEN v.producto_id = -1 OR LOWER(COALESCE(c.tipo, v.tipo, '')) LIKE '%recarga%' THEN 'recargas'
            WHEN LOWER(COALESCE(c.tipo, v.tipo, '')) LIKE '%no tradicional%' THEN 'no_tradicional'
            ELSE 'tradicional'
        END";
    }

    private function variacion(float $actual, float $anterior): ?float
    {
        return $anterior == 0.0 ? null : (($actual - $anterior) / abs($anterior)) * 100;
    }
}
