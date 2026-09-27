<?php

namespace Tests\Feature;

use Tests\TestCase;

class LotedomVentasProductoSessionTest extends TestCase
{
    public function test_session_login_and_product_sales_use_the_configured_cookie(): void
    {
        $tokenController = file_get_contents(app_path('Http/Controllers/TokenController.php'));
        $salesController = file_get_contents(app_path('Http/Controllers/VentasProductosController.php'));

        $this->assertIsString($tokenController);
        $this->assertIsString($salesController);
        $this->assertMatchesRegularExpression(
            '~https://lotedom-api\\.orkapi\\.net/api/finan/sessions~',
            $tokenController
        );
        $this->assertStringContainsString("'username' => 'api_contabilidad@bsh'", $tokenController);

        preg_match("~Cookie: (_orkapi_session=[^']+)~", $tokenController, $loginCookie);
        preg_match("~Cookie: (_orkapi_session=[^']+)~", $salesController, $salesCookie);

        $this->assertArrayHasKey(1, $loginCookie);
        $this->assertArrayHasKey(1, $salesCookie);
        $this->assertSame($loginCookie[1], $salesCookie[1]);
        $this->assertSame(
            'f17b28f6ee86fdf6f4466f1d390b5f7a9397fd8623c52b3b0e81d06233cc4fee',
            hash('sha256', $loginCookie[1])
        );
    }
}
