<?php

namespace App\Http\Controllers;

use App\Http\Requests\BiVentasGlobalRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiVentasGlobalController extends Controller
{
    /**
     * Plataformas consolidadas y tablas que alimentan sus filtros.
     *
     * @var array<string, array{nombre: string, agencias: string}>
     */
    private const PLATAFORMAS = [
        'real' => ['nombre' => 'Lotobet Real', 'agencias' => 'agencias'],
        'lotodom' => ['nombre' => 'Lotodom', 'agencias' => 'agencias_lotedom'],
        'delta' => ['nombre' => 'Delta', 'agencias' => 'agencias_delta'],
    ];

    public function index(): View
    {
        $ultimaFecha = collect([
            DB::table('ventas_usuarios_bet')->max('fecha'),
            DB::table('ventas_usuarios_net')->max('fecha'),
            DB::table('ventas_flash_bet')->max('fecha'),
        ])->filter()->max() ?? now()->toDateString();

        return view('bi.ventas-global', [
            'plataformas' => array_map(fn (array $plataforma): string => $plataforma['nombre'], self::PLATAFORMAS),
            'ultimaFecha' => CarbonImmutable::parse($ultimaFecha)->toDateString(),
            'filtros' => [
                'grupos' => $this->opcionesAgencia('grupo'),
                'centrales' => $this->opcionesAgencia('central'),
                'gerentes' => $this->opcionesAgencia('gerente_de_servicio'),
                'tiposPago' => $this->opcionesAgencia('tipo_pago'),
            ],
        ]);
    }

    public function data(BiVentasGlobalRequest $request): JsonResponse
    {
        $hasta = CarbonImmutable::parse($request->validated('fecha_hasta'));
        $sistema = $request->validated('sistema');

        $consultas = [
            'real' => fn (): array => app(BiLotobetController::class)->data($request)->getData(true),
            'lotodom' => fn (): array => app(BiPlataformaController::class)->data($request, 'lotodom')->getData(true),
            'delta' => fn (): array => app(BiPlataformaController::class)->data($request, 'delta')->getData(true),
        ];

        $resultados = collect($consultas)
            ->when($sistema, fn ($consultas) => $consultas->only([$sistema]))
            ->map(fn (callable $consulta): array => $consulta())
            ->all();

        $plataformas = collect($resultados)->map(function (array $resultado, string $key): array {
            return [
                'key' => $key,
                'nombre' => self::PLATAFORMAS[$key]['nombre'],
                'terminales' => $resultado['terminales'],
                'venta' => $resultado['venta_global']['total'],
                'venta_anterior' => $resultado['venta_global']['anterior'],
                'promedio_terminal' => $resultado['venta_global']['promedio_terminal'],
                'variacion' => $resultado['venta_global']['variacion'],
                'premios' => $resultado['premios']['total'],
                'resultado_bruto' => $resultado['resultado_bruto']['total'],
            ];
        })->values();

        $terminales = [
            'total' => $plataformas->sum('terminales.total'),
            'activas' => $plataformas->sum('terminales.activas'),
            'inactivas' => $plataformas->sum('terminales.inactivas'),
            'vendieron' => $plataformas->sum('terminales.vendieron'),
            'sin_ventas' => $plataformas->sum('terminales.sin_ventas'),
        ];

        $venta = (float) $plataformas->sum('venta');
        $ventaAnterior = (float) $plataformas->sum('venta_anterior');
        $premios = (float) collect($resultados)->sum('premios.total');
        $premiosAnteriores = (float) collect($resultados)->sum('premios.anterior');
        $resultado = $venta - $premios;

        $grafico = collect(range(0, 6))->map(function (int $offset) use ($resultados, $hasta): array {
            $fecha = $hasta->subDays(6 - $offset);
            $dia = [
                'fecha' => $fecha->toDateString(),
                'dia' => ucfirst($fecha->locale('es')->dayName),
            ];

            foreach ($resultados as $key => $resultadoPlataforma) {
                $dia[$key] = (float) $resultadoPlataforma['grafico'][$offset]['total'];
            }

            return $dia + ['total' => array_sum(array_intersect_key($dia, $resultados))];
        })->values();

        return response()->json([
            'terminales' => $terminales,
            'venta_global' => [
                'total' => $venta,
                'promedio_terminal' => $terminales['total'] > 0 ? $venta / $terminales['total'] : 0,
                'variacion' => $this->variacion($venta, $ventaAnterior),
            ],
            'premios' => [
                'total' => $premios,
                'promedio_terminal' => $terminales['total'] > 0 ? $premios / $terminales['total'] : 0,
                'variacion' => $this->variacion($premios, $premiosAnteriores),
            ],
            'resultado_bruto' => [
                'total' => $resultado,
                'promedio_terminal' => $terminales['total'] > 0 ? $resultado / $terminales['total'] : 0,
                'variacion' => $this->variacion($resultado, $ventaAnterior - $premiosAnteriores),
            ],
            'plataformas' => $plataformas,
            'grafico' => $grafico,
        ]);
    }

    /** @return array<int, string> */
    private function opcionesAgencia(string $column): array
    {
        return collect(self::PLATAFORMAS)
            ->flatMap(fn (array $plataforma): array => DB::table($plataforma['agencias'])->whereNotNull($column)
                ->whereRaw("TRIM({$column}) <> ''")->distinct()->pluck($column)->all())
            ->unique()->sort()->values()->all();
    }

    private function variacion(float $actual, float $anterior): ?float
    {
        return $anterior == 0.0 ? null : (($actual - $anterior) / abs($anterior)) * 100;
    }
}
