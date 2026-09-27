<?php

namespace Tests\Feature;

use Tests\TestCase;

class RecargasLotedomConfigurationTest extends TestCase
{
    public function test_query_and_save_match_the_confirmed_lotedom_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/RecargasController.php'));

        $this->assertIsString($controller);
        $this->assertSame(
            3,
            substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/ventas_recarga/{$fecha}')
        );
        $this->assertStringNotContainsString('http://contable.apploteka.com/api/finan/ventas_recarga/', $controller);

        $this->assertSame(3, substr_count($controller, '$this->lotedomSessionHeaders()'));
        $this->assertStringContainsString(
            "CURLOPT_URL => 'https://lotedom-api.orkapi.net/api/finan/sessions'",
            $controller
        );
        $this->assertStringContainsString("'token: '.trim(\$token)", $controller);
        $this->assertStringContainsString("'Cookie: '.\$cookie", $controller);
    }
}
