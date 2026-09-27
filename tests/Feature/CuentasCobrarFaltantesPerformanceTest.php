<?php

namespace Tests\Feature;

use Tests\TestCase;

class CuentasCobrarFaltantesPerformanceTest extends TestCase
{
    public function test_division_catalog_is_scoped_to_faltantes_account_and_cached(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/ContabilidadCuentasCobrarFaltantesController.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString("Cache::remember('contabilidad.cxc_faltantes.divisiones.v2'", $controller);
        $this->assertStringContainsString("->where('cuenta', self::CUENTA_FALTANTES)", $controller);
        $this->assertStringNotContainsString("groupBy(DB::raw('TRIM(id_division)'))", $controller);
    }

    public function test_migration_adds_indexes_used_by_report_queries(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_09_26_141724_add_cuentas_cobrar_faltantes_query_indexes.php'));

        $this->assertIsString($migration);
        $this->assertStringContainsString("['cuenta', 'fecha', 'id_centro_costo']", $migration);
        $this->assertStringContainsString("['cuenta', 'id_division']", $migration);
        $this->assertStringContainsString("index('id_viejo'", $migration);
    }
}
