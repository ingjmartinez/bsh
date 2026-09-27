<?php

namespace Tests\Feature;

use Tests\TestCase;

class PagosMismaEmpresaLotedomConfigurationTest extends TestCase
{
    public function test_lotedom_company_payments_query_and_save_use_the_confirmed_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/PagoMismaEmpresaController.php'));

        $this->assertIsString($controller);
        $this->assertSame(
            2,
            substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/pagos_empresa/{$fecha}')
        );
        $this->assertStringNotContainsString('http://contable.apploteka.com/api/finan/pagos_empresa/', $controller);

        preg_match_all("~'token: ([^']+)'~", $controller, $tokens);
        preg_match_all("~'Cookie: (_orkapi_session=[^']+)'~", $controller, $cookies);

        $this->assertCount(2, $tokens[1]);
        $this->assertCount(2, $cookies[1]);
        $this->assertSame($tokens[1][0], $tokens[1][1]);
        $this->assertSame($cookies[1][0], $cookies[1][1]);
        $this->assertSame(
            '0b50142d7c50ca3901d53c67e6b179baed1c0e9e325d7e2659765113ef29c78f',
            hash('sha256', $tokens[1][0])
        );
        $this->assertSame(
            'e6013fc5640b7edcaa9256b6362247e7bb377034d140e7378621c07f3f1b5b2d',
            hash('sha256', $cookies[1][0])
        );
    }
}
