<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReporteVentasAgenciaLotobetTest extends TestCase
{
    public function test_ventas_agencia_periodo_lotobet_uses_ventas_usuarios_bet(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withArgs(function (string $query, array $bindings): bool {
                return str_contains($query, 'FROM ventas_usuarios_bet v')
                    && ! str_contains($query, 'ventas_producto_bet')
                    && $bindings === ['2026-09-01', '2026-09-25'];
            })
            ->andReturn([]);

        $this->withoutMiddleware()
            ->getJson('/reportes-ventas-agencia-periodo/list?'.http_build_query([
                'sistema' => 'Lotobet',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-25',
            ]))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_ventas_agencia_periodo_lotonet_keeps_ventas_producto_net(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withArgs(fn (string $query): bool => str_contains($query, 'FROM ventas_producto_net v'))
            ->andReturn([]);

        $this->withoutMiddleware()
            ->getJson('/reportes-ventas-agencia-periodo/list?'.http_build_query([
                'sistema' => 'Lotonet',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-25',
            ]))
            ->assertOk();
    }

    public function test_ventas_por_agencia_lotobet_uses_ventas_usuarios_bet(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withArgs(function (string $query, array $bindings): bool {
                return str_contains($query, 'FROM ventas_usuarios_bet v')
                    && ! str_contains($query, 'ventas_producto_bet')
                    && $bindings === ['2026-09-01', '2026-09-25', '1001'];
            })
            ->andReturn([]);

        $this->withoutMiddleware()
            ->getJson('/reportes-ventas-por-agencia/list?'.http_build_query([
                'sistema' => 'Lotobet',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-25',
                'terminal' => '1001',
            ]))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_ventas_por_agencia_without_terminal_returns_empty_without_querying(): void
    {
        DB::shouldReceive('select')->never();

        $this->withoutMiddleware()
            ->getJson('/reportes-ventas-por-agencia/list?'.http_build_query([
                'sistema' => 'Lotobet',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-25',
            ]))
            ->assertOk()
            ->assertExactJson([]);
    }
}
