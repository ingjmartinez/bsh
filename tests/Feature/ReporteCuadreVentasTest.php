<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReporteCuadreVentasTest extends TestCase
{
    public function test_lotobet_report_uses_ventas_usuarios_bet_table(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withArgs(function (string $query, array $bindings): bool {
                return str_contains($query, 'FROM ventas_usuarios_bet v')
                    && ! str_contains($query, 'FROM ventas_producto_bet v')
                    && $bindings === ['2026-09-01', '2026-09-25', '2026-09-01', '2026-09-25'];
            })
            ->andReturn([]);

        $this->withoutMiddleware()
            ->getJson('/reportes-cuadre-ventas/list?'.http_build_query([
                'sistema' => 'Lotobet',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-25',
            ]))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_lotedom_report_uses_ventas_usuarios_net_table(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withArgs(function (string $query, array $bindings): bool {
                return str_contains($query, 'FROM ventas_usuarios_net v')
                    && ! str_contains($query, 'FROM ventas_producto_net v')
                    && $bindings === ['2026-09-01', '2026-09-25', '2026-09-01', '2026-09-25'];
            })
            ->andReturn([]);

        $this->withoutMiddleware()
            ->getJson('/reportes-cuadre-ventas/list?'.http_build_query([
                'sistema' => 'Lotedom',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-25',
            ]))
            ->assertOk()
            ->assertExactJson([]);
    }
}
