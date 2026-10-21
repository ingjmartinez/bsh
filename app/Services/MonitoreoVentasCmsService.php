<?php

namespace App\Services;

use App\Models\Agencia;
use App\Models\MonitoreoVentaAsignacion;
use App\Models\MonitoreoVentaCaso;
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
        $generados = 0;

        Agencia::query()->where('estatus', 1)->whereNotNull('terminal')
            ->chunkById(300, function ($agencias) use ($porTerminal, $asignaciones, $fechaDia, &$generados): void {
                foreach ($agencias as $agencia) {
                    $terminal = $this->terminalKey((string) $agencia->terminal);
                    if ($terminal === '0') {
                        continue;
                    }

                    $totales = array_fill_keys(array_keys(self::UMBRALES), 0.0);
                    foreach ($porTerminal->get($terminal, collect()) as $venta) {
                        $totales[$venta->categoria] = (float) $venta->total;
                    }

                    if (! $this->incumple($totales)) {
                        continue;
                    }

                    $asignacion = $asignaciones->get($this->asignacionKey((string) $agencia->central, (string) $agencia->grupo));
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
    public function incumple(array $totales): bool
    {
        foreach (self::UMBRALES as $categoria => $umbral) {
            if (($totales[$categoria] ?? 0) < $umbral) {
                return true;
            }
        }

        return false;
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
