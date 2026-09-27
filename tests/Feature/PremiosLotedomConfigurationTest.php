<?php

namespace Tests\Feature;

use Tests\TestCase;

class PremiosLotedomConfigurationTest extends TestCase
{
    public function test_lotedom_prizes_query_and_save_use_the_confirmed_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/PremioController.php'));

        $this->assertIsString($controller);
        $this->assertSame(
            2,
            substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/premios/{$fecha}')
        );
        $this->assertStringNotContainsString('http://contable.apploteka.com/api/finan/premios/', $controller);
        $this->assertStringNotContainsString('CURLOPT_POSTFIELDS', $controller);

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
            'cdbe016671e08a176506b540a255f0295a3dfb2d0037b8d8a3d7798d60f8ee42',
            hash('sha256', $cookies[1][0])
        );
    }
}
