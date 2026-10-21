<?php

namespace Tests\Feature;

use Tests\TestCase;

class FinanceDashboardLotedomTableTest extends TestCase
{
    public function test_lotedom_dashboard_reads_from_ventas_usuarios_net(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/FinanceDashboardController.php'));

        $this->assertIsString($controller);
        $this->assertStringNotContainsString('vt_usuarios_net', $controller);
        $this->assertSame(
            2,
            substr_count($controller, "\$plataforma === 'net' ? 'ventas_usuarios_net' : 'ventas_usuarios_bet'")
        );
    }
}
