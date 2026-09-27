<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaqueticoLotedomConfigurationTest extends TestCase
{
    public function test_query_and_save_use_the_confirmed_productos_externos_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/PaqueticoController.php'));

        $this->assertIsString($controller);
        $this->assertSame(
            2,
            substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/productos_externos/{$fecha}')
        );
        $this->assertStringNotContainsString('http://contable.apploteka.com/api/finan/compra_paqueticos/', $controller);
        $this->assertStringNotContainsString('CURLOPT_POSTFIELDS', $controller);
        $this->assertStringNotContainsString("\$v['identificacion']", $controller);
        $this->assertStringContainsString("private const PRODUCTO_ID = 'PAQUETICOS_ZATACA';", $controller);
        $this->assertSame(
            2,
            substr_count($controller, "(\$row['producto_id'] ?? null) === self::PRODUCTO_ID")
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
            'b32d4f75a9eb0a359c37f3e8cc8c0b4f862ac8e2e40af991d742f715d6d304aa',
            hash('sha256', $cookies[1][0])
        );
    }

    public function test_data_table_uses_all_endpoint_fields_in_json_order(): void
    {
        $view = file_get_contents(resource_path('views/lotedom/paquetico.blade.php'));

        $this->assertIsString($view);

        $fields = [
            'fecha',
            'consorcio_id',
            'consorcio_codigo',
            'consorcio_nombre',
            'banca_id',
            'banca_nombre',
            'producto_id',
            'producto_nombre',
            'descripcion',
            'monto',
            'pago',
            'terminal_codigo',
            'agencia_id',
            'terminal_nombre',
            'distribuidora_id',
            'distribuidora_nombre',
            'proveedor_id',
            'proveedor_nombre',
        ];

        $lastPosition = -1;

        foreach ($fields as $field) {
            $position = strpos($view, "item.{$field}", $lastPosition + 1);

            $this->assertNotFalse($position, "No se encontró el campo {$field} en el DataTable.");
            $this->assertGreaterThan($lastPosition, $position, "El campo {$field} está fuera del orden del JSON.");

            $lastPosition = $position;
        }
    }
}
