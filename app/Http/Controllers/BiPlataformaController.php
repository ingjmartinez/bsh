<?php

namespace App\Http\Controllers;

use App\Http\Requests\BiLotobetDashboardRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiPlataformaController extends Controller
{
    /**
     * Fuentes de datos de cada plataforma del BI.
     *
     * @var array<string, array{nombre: string, agencias: string, ventas: string, terminal: string, monto: string, premios: string, categorias: array<string, string>}>
     */
    private const PLATAFORMAS = [
        'lotodom' => [
            'nombre' => 'Lotodom',
            'agencias' => 'agencias_lotedom',
            'ventas' => 'ventas_usuarios_net',
            'terminal' => 'TRIM(v.agencia_id)',
            'monto' => 'v.monto',
            'premios' => 'premios_net',
            'categorias' => [
                'tradicional' => 'Tradicional',
                'recargas' => 'Recargas',
                'raza' => 'Raza',
                'no_tradicional' => 'No tradicionales',
                'paqueticos' => 'Paqueticos',
            ],
        ],
        'delta' => [
            'nombre' => 'Delta',
            'agencias' => 'agencias_delta',
            'ventas' => 'ventas_flash_bet',
            'terminal' => 'TRIM(CAST(v.numero_externo AS CHAR))',
            'monto' => 'COALESCE(v.venta_loteria, 0) + COALESCE(v.venta_recarga, 0) + COALESCE(v.ventas_no_tradicional, 0)',
            'premios' => 'COALESCE(v.premios_pagado, 0) + COALESCE(v.premios_pagados_no_tradicional, 0)',
            'categorias' => [
                'tradicional' => 'Tradicional',
                'recargas' => 'Recargas',
                'no_tradicional' => 'No tradicionales',
            ],
        ],
    ];

    public function index(string $plataforma): View
    {
        $config = self::PLATAFORMAS[$plataforma];
        $ultimaFecha = DB::table($config['ventas'])->max('fecha') ?? now()->toDateString();

        return view('bi.plataforma', [
            'plataforma' => $plataforma,
            'nombre' => $config['nombre'],
            'categorias' => $config['categorias'],
            'ultimaFecha' => CarbonImmutable::parse($ultimaFecha)->toDateString(),
            'filtros' => [
                'grupos' => $this->opcionesAgencia($config['agencias'], 'grupo'),
                'centrales' => $this->opcionesAgencia($config['agencias'], 'central'),
                'gerentes' => $this->opcionesAgencia($config['agencias'], 'gerente_de_servicio'),
                'tiposPago' => $this->opcionesAgencia($config['agencias'], 'tipo_pago'),
            ],
        ]);
    }

    public function data(BiLotobetDashboardRequest $request, string $plataforma): JsonResponse
    {
        $config = self::PLATAFORMAS[$plataforma];
        $filters = $request->validated();
        $desde = CarbonImmutable::parse($filters['fecha_desde']);
        $hasta = CarbonImmutable::parse($filters['fecha_hasta']);
        $comparacionDesde = $desde->subWeek();
        $comparacionHasta = $hasta->subWeek();

        $terminales = $this->agenciasQuery($config, $filters);
        $totalTerminales = (clone $terminales)->count();
        $activas = (clone $terminales)->where('a.estatus', 1)->count();

        $terminalesVendieron = $this->ventasQuery($config, $filters, $desde, $hasta)
            ->whereNotNull('a.id')
            ->whereRaw("({$config['monto']}) > 0")
            ->selectRaw("DISTINCT {$config['terminal']} AS terminal");
        $vendieron = DB::query()->fromSub($terminalesVendieron, 'terminales')->count();

        $ventaGlobal = $this->totalVentas($config, $filters, $desde, $hasta);
        $ventasPrevias = $this->totalVentas($config, $filters, $comparacionDesde, $comparacionHasta);
        $premios = $this->totalPremios($config, $filters, $desde, $hasta);
        $premiosPrevios = $this->totalPremios($config, $filters, $comparacionDesde, $comparacionHasta);

        $actuales = $this->totalesPorCategoria($plataforma, $config, $filters, $desde, $hasta);
        $previas = $this->totalesPorCategoria($plataforma, $config, $filters, $comparacionDesde, $comparacionHasta);

        $categorias = collect(array_keys($config['categorias']))
            ->mapWithKeys(function (string $categoria) use ($actuales, $previas, $totalTerminales): array {
                $actual = (float) ($actuales[$categoria] ?? 0);

                return [$categoria => [
                    'total' => $actual,
                    'promedio_terminal' => $totalTerminales > 0 ? $actual / $totalTerminales : 0,
                    'variacion' => $this->variacion($actual, (float) ($previas[$categoria] ?? 0)),
                ]];
            });

        $graficoDesde = $hasta->subDays(6);
        $ventasDiarias = $this->ventasQuery($config, $filters, $graficoDesde, $hasta)
            ->selectRaw("DATE(v.fecha) AS fecha, SUM({$config['monto']}) AS total")
            ->groupByRaw('DATE(v.fecha)')
            ->pluck('total', 'fecha');
        $grafico = collect(range(0, 6))->map(function (int $offset) use ($graficoDesde, $ventasDiarias): array {
            $fecha = $graficoDesde->addDays($offset);

            return [
                'fecha' => $fecha->toDateString(),
                'dia' => ucfirst($fecha->locale('es')->dayName),
                'total' => (float) ($ventasDiarias[$fecha->toDateString()] ?? 0),
            ];
        })->values();

        $resultado = $ventaGlobal - $premios;

        return response()->json([
            'terminales' => [
                'total' => $totalTerminales,
                'activas' => $activas,
                'inactivas' => max(0, $totalTerminales - $activas),
                'vendieron' => $vendieron,
                'sin_ventas' => max(0, $totalTerminales - $vendieron),
            ],
            'venta_global' => [
                'total' => $ventaGlobal,
                'promedio_terminal' => $totalTerminales > 0 ? $ventaGlobal / $totalTerminales : 0,
                'anterior' => $ventasPrevias,
                'variacion' => $this->variacion($ventaGlobal, $ventasPrevias),
            ],
            'premios' => [
                'total' => $premios,
                'promedio_terminal' => $totalTerminales > 0 ? $premios / $totalTerminales : 0,
                'anterior' => $premiosPrevios,
                'variacion' => $this->variacion($premios, $premiosPrevios),
                'con_datos' => $premios > 0,
            ],
            'categorias' => $categorias,
            'resultado_bruto' => [
                'total' => $resultado,
                'promedio_terminal' => $totalTerminales > 0 ? $resultado / $totalTerminales : 0,
                'variacion' => $this->variacion($resultado, $ventasPrevias - $premiosPrevios),
            ],
            'grafico' => $grafico,
        ]);
    }

    /** @return array<int, string> */
    private function opcionesAgencia(string $tabla, string $column): array
    {
        return DB::table($tabla)->whereNotNull($column)
            ->whereRaw("TRIM({$column}) <> ''")->distinct()->orderBy($column)->pluck($column)->all();
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $filters
     */
    private function agenciasQuery(array $config, array $filters): Builder
    {
        return $this->applyAgencyFilters(DB::table("{$config['agencias']} as a"), $filters);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $filters
     */
    private function ventasQuery(array $config, array $filters, CarbonImmutable $desde, CarbonImmutable $hasta): Builder
    {
        $query = DB::table("{$config['ventas']} as v")
            ->leftJoin("{$config['agencias']} as a", DB::raw('TRIM(a.terminal)'), '=', DB::raw($config['terminal']))
            ->whereBetween('v.fecha', [$desde->toDateString(), $hasta->toDateString()]);

        return $this->applyAgencyFilters($query, $filters);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $filters
     */
    private function totalVentas(array $config, array $filters, CarbonImmutable $desde, CarbonImmutable $hasta): float
    {
        return (float) $this->ventasQuery($config, $filters, $desde, $hasta)->sum(DB::raw($config['monto']));
    }

    /**
     * Lotodom lee sus premios de premios_net; Delta los trae en la misma fila de ventas.
     *
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $filters
     */
    private function totalPremios(array $config, array $filters, CarbonImmutable $desde, CarbonImmutable $hasta): float
    {
        if ($config['ventas'] === 'ventas_flash_bet') {
            return (float) $this->ventasQuery($config, $filters, $desde, $hasta)->sum(DB::raw($config['premios']));
        }

        $query = DB::table("{$config['premios']} as p")
            ->leftJoin("{$config['agencias']} as a", DB::raw('TRIM(a.terminal)'), '=', DB::raw('TRIM(p.agencia_id)'))
            ->whereBetween('p.fecha', [$desde->toDateString(), $hasta->toDateString()]);

        return (float) $this->applyAgencyFilters($query, $filters)->sum('p.monto');
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $filters
     * @return array<string, float>
     */
    private function totalesPorCategoria(string $plataforma, array $config, array $filters, CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        if ($plataforma === 'delta') {
            $totales = $this->ventasQuery($config, $filters, $desde, $hasta)
                ->selectRaw('COALESCE(SUM(v.venta_loteria), 0) AS tradicional')
                ->selectRaw('COALESCE(SUM(v.venta_recarga), 0) AS recargas')
                ->selectRaw('COALESCE(SUM(v.ventas_no_tradicional), 0) AS no_tradicional')
                ->first();

            return [
                'tradicional' => (float) $totales->tradicional,
                'recargas' => (float) $totales->recargas,
                'no_tradicional' => (float) $totales->no_tradicional,
            ];
        }

        $bucket = $this->bucketExpression();

        return $this->ventasQuery($config, $filters, $desde, $hasta)
            ->leftJoin('catalogo_juegos as c', 'c.producto_id', '=', 'v.producto_id')
            ->selectRaw("{$bucket} AS categoria, SUM(v.monto) AS total")
            ->groupByRaw($bucket)
            ->pluck('total', 'categoria')
            ->map(fn ($total): float => (float) $total)
            ->all();
    }

    /** @param array<string, mixed> $filters */
    private function applyAgencyFilters(Builder $query, array $filters): Builder
    {
        foreach (['grupo' => 'grupo', 'central' => 'central', 'gerente' => 'gerente_de_servicio', 'tipo_pago' => 'tipo_pago'] as $key => $column) {
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
