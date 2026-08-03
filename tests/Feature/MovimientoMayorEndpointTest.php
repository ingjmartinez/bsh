<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MovimientoMayorEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $migration = require database_path('migrations/2026_08_03_112651_ensure_entradas_diario_storage_is_ready.php');
        $migration->up();
    }

    public function test_report_page_renders(): void
    {
        $this->withoutMiddleware()
            ->get('/contabilidad/movimiento-mayor')
            ->assertOk()
            ->assertSee('Movimiento del Mayor');
    }

    public function test_data_endpoint_returns_summary_and_rows(): void
    {
        DB::table('entradas_diario')->insert([
            'external_key' => sha1('movimiento-prueba'),
            'no_asiento' => 'AS-100',
            'company_id' => '126',
            'fecha' => '2026-07-05',
            'cuenta' => '610101',
            'debito' => 125.50,
            'credito' => 25.25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withoutMiddleware()
            ->getJson('/api-entradas-diario?empresa=126&fecha_inicio=2026-07-05&fecha_fin=2026-07-05')
            ->assertOk()
            ->assertJsonPath('summary.registros', 1)
            ->assertJsonPath('summary.debito', 125.5)
            ->assertJsonPath('summary.credito', 25.25)
            ->assertJsonPath('displayed', 1)
            ->assertJsonPath('data.0.NoAsiento', 'AS-100');
    }

    public function test_data_endpoint_returns_service_unavailable_when_storage_is_missing(): void
    {
        Schema::drop('entradas_diario');

        $this->withoutMiddleware()
            ->getJson('/api-entradas-diario?empresa=126&fecha_inicio=2026-07-05&fecha_fin=2026-07-05')
            ->assertStatus(503)
            ->assertJsonPath(
                'message',
                'El almacenamiento de movimiento del mayor no esta disponible. Verifica las migraciones del servidor.'
            );
    }

    public function test_storage_repair_migration_is_idempotent(): void
    {
        $migration = require database_path('migrations/2026_08_03_112651_ensure_entradas_diario_storage_is_ready.php');
        $migration->up();

        $indexNames = collect(Schema::getIndexes('entradas_diario'))->pluck('name');

        $this->assertTrue(Schema::hasColumn('entradas_diario', 'id_viejo'));
        $this->assertTrue($indexNames->contains('entradas_diario_id_viejo_index'));
    }

    public function test_legacy_index_migration_tolerates_a_missing_table(): void
    {
        Schema::drop('entradas_diario');

        $migration = require database_path('migrations/2026_07_09_120000_add_id_viejo_index_to_entradas_diario_table.php');
        $migration->up();

        $this->assertFalse(Schema::hasTable('entradas_diario'));
    }
}
