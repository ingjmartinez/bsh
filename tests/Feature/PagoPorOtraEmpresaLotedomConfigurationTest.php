<?php

namespace Tests\Feature;

use Tests\TestCase;

class PagoPorOtraEmpresaLotedomConfigurationTest extends TestCase
{
    public function test_lotedom_payments_from_another_company_query_and_save_use_the_confirmed_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/PagoPorOtraEmpresaController.php'));

        $this->assertIsString($controller);
        $this->assertSame(
            2,
            substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/pagos_por_otra_empresa/{$fecha}')
        );
        $this->assertStringNotContainsString(
            'http://lotedom-api.orkapi.net/api/finan/pagos_por_otra_empresa/{$fecha}',
            $controller
        );

        preg_match_all("~'token: ([^']+)'~", $controller, $tokens);
        preg_match_all("~'Cookie: (_orkapi_session=[^']+)'~", $controller, $cookies);

        $this->assertCount(2, $tokens[1]);
        $this->assertCount(2, $cookies[1]);
        $this->assertSame($tokens[1][0], $tokens[1][1]);
        $this->assertSame($cookies[1][0], $cookies[1][1]);
        $this->assertSame('ZFozLWdBYyqERusVdTsW', $tokens[1][0]);
        $this->assertStringStartsWith('_orkapi_session=I23kNjSEGE2KzPx27uRwhhGwosA6jItj', $cookies[1][0]);
        $this->assertStringEndsWith('--3L8CmHP3nE50LG4oDBvWXQ%3D%3D', $cookies[1][0]);
    }

    public function test_query_returns_the_lotedom_fields_used_by_the_data_table(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/PagoPorOtraEmpresaController.php'));
        $view = file_get_contents(resource_path('views/lotedom/pagos-porotra-empresa.blade.php'));

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertStringContainsString(
            "\$data = is_array(\$ventas['data']['result'] ?? null) ? \$ventas['data']['result'] : [];",
            $controller
        );

        $fields = [
            'fecha',
            'consorcio_id',
            'consorcio_codigo',
            'producto_id',
            'producto_nombre',
            'monto',
            'venta_terminal_codigo',
            'pagado_por_consorcio_id',
        ];
        $lastPosition = -1;

        foreach ($fields as $field) {
            $position = strpos($view, "item.{$field}", $lastPosition + 1);

            $this->assertNotFalse($position, "No se encontro el campo {$field} en el DataTable.");
            $this->assertGreaterThan($lastPosition, $position, "El campo {$field} esta fuera del orden del JSON.");

            $lastPosition = $position;
        }
    }

    public function test_controller_does_not_report_upstream_errors_as_empty_successful_results(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/PagoPorOtraEmpresaController.php'));

        $this->assertIsString($controller);
        $this->assertSame(2, substr_count($controller, "if (\$code !== '00' && \$code !== '0')"));
        $this->assertStringContainsString('if ($response === false || $httpCode >= 400)', $controller);
        $this->assertStringContainsString("'message' => 'No fue posible obtener los pagos por otra empresa.'", $controller);
    }
}
