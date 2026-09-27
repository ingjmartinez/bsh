<?php

namespace Tests\Feature;

use App\Models\PagoAOtraEmpresaNet;
use App\Support\LotedomRowMapper;
use Tests\TestCase;

class PagosAOtraEmpresaLotedomConfigurationTest extends TestCase
{
    public function test_query_and_save_use_the_confirmed_lotedom_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/PagoAOtraEmpresaController.php'));

        $this->assertIsString($controller);
        $this->assertSame(
            2,
            substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/pagos_a_otra_empresa/{$fecha}')
        );
        $this->assertStringNotContainsString(
            'http://contable.apploteka.com/api/finan/pagos_a_otra_empresa/',
            $controller
        );

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
            'f9215350794ebf843427eda4309507ca310033ea0d37d934e51f71a4084f6ca0',
            hash('sha256', $cookies[1][0])
        );
    }

    public function test_data_table_uses_the_endpoint_fields_in_json_order(): void
    {
        $view = file_get_contents(resource_path('views/lotedom/pagos-aotra-empresa.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/PagoAOtraEmpresaController.php'));

        $this->assertIsString($view);
        $this->assertIsString($controller);
        $this->assertStringContainsString(
            "\$data = is_array(\$ventas) ? (\$ventas['data']['result'] ?? []) : [];",
            $controller
        );

        $fields = [
            'fecha',
            'consorcio_id',
            'consorcio_codigo',
            'producto_id',
            'producto_nombre',
            'monto',
            'pago_terminal_codigo',
            'pagado_a_consorcio_id',
        ];
        $lastPosition = -1;

        foreach ($fields as $field) {
            $position = strpos($view, "item.{$field}", $lastPosition + 1);

            $this->assertNotFalse($position, "No se encontró el campo {$field} en el DataTable.");
            $this->assertGreaterThan($lastPosition, $position, "El campo {$field} está fuera del orden del JSON.");

            $lastPosition = $position;
        }
    }

    public function test_endpoint_row_maps_to_the_existing_database_columns(): void
    {
        $mapped = LotedomRowMapper::pago([
            'fecha' => '2026-09-24',
            'producto_nombre' => 'QUINIELA',
            'monto' => '120.0',
            'pago_terminal_codigo' => '20310105',
        ], '2026-09-24');

        $this->assertSame('20310105', $mapped['agencia_id']);
        $this->assertSame(120.0, $mapped['monto']);
        $this->assertSame('2026-09-24', $mapped['fecha']);
        $this->assertNull($mapped['cedula']);
        $this->assertSame('QUINIELA', $mapped['tipo_pago']);

        $model = new PagoAOtraEmpresaNet;

        $this->assertSame('id', $model->getKeyName());
        $this->assertSame(
            ['agencia_id', 'monto', 'fecha', 'cedula', 'tipo_pago'],
            $model->getFillable()
        );
    }
}
