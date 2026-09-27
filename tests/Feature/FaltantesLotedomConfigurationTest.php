<?php

namespace Tests\Feature;

use Tests\TestCase;

class FaltantesLotedomConfigurationTest extends TestCase
{
    public function test_lotedom_shortages_query_and_save_use_the_confirmed_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/FaltantesController.php'));

        $this->assertIsString($controller);
        $this->assertSame(
            2,
            substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/faltantes_usuario/{$fecha}')
        );
        $this->assertStringNotContainsString('http://contable.apploteka.com/api/finan/faltantes_usuario/', $controller);

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
            '8c107618444beaa8016af411a8c797e804bb836c64669c191aef3a2a8ccdeb42',
            hash('sha256', $cookies[1][0])
        );
    }

    public function test_query_returns_the_original_lotedom_fields_used_by_the_table(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/FaltantesController.php'));
        $view = file_get_contents(resource_path('views/lotedom/faltantes.blade.php'));

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertStringContainsString("\$row['monto'] = abs((float) (\$row['monto'] ?? 0));", $controller);

        foreach (['consorcio_id', 'consorcio_codigo', 'codigo', 'identificacion', 'monto', 'fecha'] as $field) {
            $this->assertStringContainsString("item.{$field}", $view);
        }
    }

    public function test_upstream_errors_are_not_reported_as_successful_empty_results(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/FaltantesController.php'));

        $this->assertIsString($controller);
        $this->assertSame(2, substr_count($controller, "if (\$code !== '00' && \$code !== '0')"));
        $this->assertStringContainsString("'message' => 'No fue posible obtener los faltantes de Lotedom.'", $controller);
        $this->assertStringContainsString("'message' => 'No fue posible guardar los faltantes de Lotedom.'", $controller);
    }
}
