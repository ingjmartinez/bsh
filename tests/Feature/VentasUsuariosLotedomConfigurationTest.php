<?php

namespace Tests\Feature;

use Tests\TestCase;

class VentasUsuariosLotedomConfigurationTest extends TestCase
{
    public function test_lotedom_user_sales_uses_the_confirmed_endpoint_and_session(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/VentasController.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString(
            'https://lotedom-api.orkapi.net/api/finan/ventas_por_usuario/{$fecha}',
            $controller
        );

        preg_match("~'token: ([^']+)'~", $controller, $token);
        preg_match("~'Cookie: (_orkapi_session=[^']+)'~", $controller, $cookie);

        $this->assertArrayHasKey(1, $token);
        $this->assertArrayHasKey(1, $cookie);
        $this->assertSame(
            '84e437fa9068c1a956133dc96bd0c2fdae5efc6b6222a1f3851102228c1e4f54',
            hash('sha256', $token[1])
        );
        $this->assertSame(
            'ed7dff9199b440ab6b962a8aedf0def290bfcb709dd0a54b8c5d22915626f66a',
            hash('sha256', $cookie[1])
        );
    }
}
