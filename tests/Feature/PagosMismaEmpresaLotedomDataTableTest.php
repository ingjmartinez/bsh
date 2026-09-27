<?php

namespace Tests\Feature;

use App\Support\LotedomRowMapper;
use Tests\TestCase;

class PagosMismaEmpresaLotedomDataTableTest extends TestCase
{
    public function test_data_table_uses_the_fields_returned_by_the_lotedom_endpoint(): void
    {
        $view = file_get_contents(resource_path('views/lotedom/pagos-misma-empresa.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/PagoMismaEmpresaController.php'));

        $this->assertIsString($view);
        $this->assertIsString($controller);
        $this->assertStringContainsString('item.consorcio_codigo', $view);
        $this->assertStringContainsString('item.pago_terminal_codigo', $view);
        $this->assertStringContainsString('item.venta_terminal_codigo', $view);
        $this->assertStringContainsString('item.producto_nombre', $view);
        $this->assertTrue(
            strpos($view, '<td>${item.fecha ?? fecha}</td>')
                < strpos($view, '<td>${item.consorcio_id}</td>')
        );
        $this->assertStringContainsString(
            "\$data = is_array(\$ventas) ? (\$ventas['data']['result'] ?? []) : [];",
            $controller
        );
    }

    public function test_endpoint_row_maps_to_existing_database_columns_when_saved(): void
    {
        $mapped = LotedomRowMapper::pago([
            'fecha' => '2026-05-28',
            'monto' => '160.0',
            'venta_terminal_codigo' => '20101012',
            'producto_nombre' => 'QUINIELA',
        ], '2026-05-28');

        $this->assertSame('20101012', $mapped['agencia_id']);
        $this->assertSame(160.0, $mapped['monto']);
        $this->assertSame('2026-05-28', $mapped['fecha']);
        $this->assertNull($mapped['cedula']);
        $this->assertSame('QUINIELA', $mapped['tipo_pago']);
    }
}
