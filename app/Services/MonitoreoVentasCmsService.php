<?php

namespace App\Services;

use App\Models\Agencia;
use App\Models\MonitoreoVentaAsignacion;
use App\Models\MonitoreoVentaCaso;
use App\Models\MonitoreoVentaUmbral;
use App\Models\VtUsuarioBet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MonitoreoVentasCmsService
{
    public const UMBRALES = ['tradicional' => 5000, 'no_tradicional' => 3000, 'recargas' => 1000];

    public function generar(Carbon $fecha): int
    {
        $fechaDia = $fecha->toDateString();
        $ventas = VtUsuarioBet::query()
            ->leftJoin('catalogo_juegos as c', 'ventas_usuarios_bet.producto_id', '=', 'c.producto_id')
            ->selectRaw("ventas_usuarios_bet.agencia_id AS terminal, CASE
                WHEN ventas_usuarios_bet.producto_id = -1 OR LOWER(COALESCE(c.tipo, ventas_usuarios_bet.tipo, '')) LIKE '%recarga%' THEN 'recargas'
                WHEN LOWER(COALESCE(c.tipo, ventas_usuarios_bet.tipo, '')) LIKE '%no tradicional%' THEN 'no_tradicional'
                ELSE 'tradicional' END AS categoria, SUM(COALESCE(ventas_usuarios_bet.monto, 0)) AS total")
            ->whereDate('ventas_usuarios_bet.fecha', $fechaDia)
            ->groupBy('ventas_usuarios_bet.agencia_id', 'categoria')
            ->get();

        if ($ventas->isEmpty()) {
            return 0;
        }

        $porTerminal = $ventas->groupBy(fn ($venta): string => $this->terminalKey((string) $venta->terminal));
        $asignaciones = MonitoreoVentaAsignacion::query()->get()
            ->keyBy(fn (MonitoreoVentaAsignacion $asignacion): string => $this->asignacionKey($asignacion->central, $asignacion->grupo));
        $umbralesPorAsesor = MonitoreoVentaUmbral::query()->get()->groupBy('asesor_id');
        $generados = 0;

        Agencia::query()->where('estatus', 1)->whereNotNull('terminal')
            ->chunkById(300, function ($agencias) use ($porTerminal, $asignaciones, $umbralesPorAsesor, $fechaDia, &$generados): void {
                foreach ($agencias as $agencia) {
                    $terminal = $this->terminalKey((string) $agencia->terminal);
                    if ($terminal === '0') {
                        continue;
                    }

                    $totales = array_fill_keys(array_keys(self::UMBRALES), 0.0);
                    foreach ($porTerminal->get($terminal, collect()) as $venta) {
                        $totales[$venta->categoria] = (float) $venta->total;
                    }

                    $asignacion = $asignaciones->get($this->asignacionKey((string) $agencia->central, (string) $agencia->grupo));
                    $umbrales = $this->umbralesAsesor($asignacion?->asesor_id, $umbralesPorAsesor);
                    if (! $this->incumple($totales, $umbrales)) {
                        continue;
                    }

                    $caso = MonitoreoVentaCaso::query()->firstOrCreate(
                        ['agencia_id' => $agencia->id, 'fecha' => Carbon::parse($fechaDia)],
                        [
                            'asesor_id' => $asignacion?->asesor_id,
                            'central' => $agencia->central,
                            'grupo' => $agencia->grupo,
                            'ruta' => $agencia->ruta,
                            'tradicional' => $totales['tradicional'],
                            'no_tradicional' => $totales['no_tradicional'],
                            'recargas' => $totales['recargas'],
                        ]
                    );

                    if ($caso->wasRecentlyCreated) {
                        $generados++;
                        $caso->seguimientos()->create([
                            'accion' => 'generada',
                            'detalle' => 'Alerta generada por ventas diarias bajo los umbrales.',
                        ]);
                    } elseif ($caso->estado !== 'cerrado') {
                        $caso->update($totales);
                    }
                }
            });

        return $generados;
    }

    /** @param array<string, float> $totales */
    public function incumple(array $totales, ?array $umbrales = null): bool
    {
        foreach ($umbrales ?? self::UMBRALES as $categoria => $umbral) {
            if (($totales[$categoria] ?? 0) < $umbral) {
                return true;
            }
        }

        return false;
    }

    public function umbralesAsesor(?int $asesorId, ?Collection $configuraciones = null): array
    {
        if ($asesorId === null) {
            return self::UMBRALES;
        }

        $configuraciones ??= MonitoreoVentaUmbral::query()->where('asesor_id', $asesorId)->get()->groupBy('asesor_id');

        return array_replace(self::UMBRALES, $configuraciones->get($asesorId, collect())
            ->pluck('monto', 'categoria')->map(fn ($monto): float => (float) $monto)->all());
    }

    /** @return Collection<int, array{asesor_id: int, asesor: string, categoria: string, meta: float, total: int, cumplen: Collection, pendientes: Collection}> */
    public function comparativaTerminales(Carbon $fecha, Collection $asignaciones, Collection $configuraciones): Collection
    {
        if ($asignaciones->isEmpty()) {
            return collect();
        }

        $agencias = Agencia::query()->where('estatus', 1)->whereNotNull('terminal')
            ->get(['id', 'terminal', 'nombre_agencia', 'nombre', 'central', 'grupo']);
        $ventas = VtUsuarioBet::query()
            ->leftJoin('catalogo_juegos as c', 'ventas_usuarios_bet.producto_id', '=', 'c.producto_id')
            ->selectRaw("ventas_usuarios_bet.agencia_id AS terminal, CASE
                WHEN ventas_usuarios_bet.producto_id = -1 OR LOWER(COALESCE(c.tipo, ventas_usuarios_bet.tipo, '')) LIKE '%recarga%' THEN 'recargas'
                WHEN LOWER(COALESCE(c.tipo, ventas_usuarios_bet.tipo, '')) LIKE '%no tradicional%' THEN 'no_tradicional'
                ELSE 'tradicional' END AS categoria, SUM(COALESCE(ventas_usuarios_bet.monto, 0)) AS total")
            ->whereDate('ventas_usuarios_bet.fecha', $fecha->toDateString())
            ->groupBy('ventas_usuarios_bet.agencia_id', 'categoria')->get()
            ->groupBy(fn ($venta): string => $this->terminalKey((string) $venta->terminal));

        return $asignaciones->groupBy('asesor_id')->flatMap(function (Collection $asignacionesAsesor, int $asesorId) use ($agencias, $ventas, $configuraciones): array {
            $claves = $asignacionesAsesor->map(fn (MonitoreoVentaAsignacion $asignacion): string => $this->asignacionKey($asignacion->central, $asignacion->grupo));
            $terminales = $agencias->filter(fn (Agencia $agencia): bool => $claves->contains($this->asignacionKey((string) $agencia->central, (string) $agencia->grupo)))
                ->unique(fn (Agencia $agencia): string => $this->terminalKey((string) $agencia->terminal));
            $umbrales = $this->umbralesAsesor($asesorId, $configuraciones);

            return collect($umbrales)->map(function (float|int $meta, string $categoria) use ($asesorId, $asignacionesAsesor, $terminales, $ventas): array {
                $resultado = $terminales->map(function (Agencia $agencia) use ($ventas, $categoria, $meta): array {
                    $monto = (float) ($ventas->get($this->terminalKey((string) $agencia->terminal), collect())->firstWhere('categoria', $categoria)?->total ?? 0);

                    return ['terminal' => $agencia->terminal, 'monto' => $monto, 'cumple' => $monto >= $meta];
                });

                return [
                    'asesor_id' => $asesorId,
                    'asesor' => $asignacionesAsesor->first()->asesor?->name ?? 'Asesor no disponible',
                    'categoria' => $categoria,
                    'meta' => (float) $meta,
                    'total' => $resultado->count(),
                    'cumplen' => $resultado->where('cumple', true)->values(),
                    'pendientes' => $resultado->where('cumple', false)->values(),
                ];
            })->values()->all();
        })->values();
    }

    /** @return Collection<int, MonitoreoVentaCaso> */
    public function rutasCriticas(Carbon $fecha): Collection
    {
        return MonitoreoVentaCaso::query()->whereDate('fecha', $fecha->toDateString())
            ->whereNotNull('ruta')->where('ruta', '<>', '')
            ->selectRaw('ruta, COUNT(DISTINCT agencia_id) AS agencias_alerta')
            ->groupBy('ruta')->havingRaw('COUNT(DISTINCT agencia_id) >= 2')
            ->orderByDesc('agencias_alerta')->get();
    }

    private function terminalKey(string $terminal): string
    {
        return ltrim(trim($terminal), '0') ?: '0';
    }

    private function asignacionKey(string $central, string $grupo): string
    {
        return mb_strtolower(trim($central).'|'.trim($grupo));
    }
}
