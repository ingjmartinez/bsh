<?php

namespace Tests\Feature;

use App\Http\Controllers\Api;
use Illuminate\Database\Schema\Blueprint;
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
            ->assertSee('Movimiento del Mayor')
            ->assertSeeInOrder(['id="entradaDiarioEmpresa"', 'id="entradaDiarioDivision"', 'id="entradaDiarioFechaInicio"'], false)
            ->assertSee("params.set('id_division', idDivision)", false);
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
            ->assertJsonPath('limit', 500)
            ->assertJsonPath('data.0.NoAsiento', 'AS-100');
    }

    public function test_division_options_and_movements_follow_the_selected_company_cost_centers(): void
    {
        Schema::create('centros_de_costo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('id_centro_costo')->unique();
            $table->string('company_id');
            $table->string('id_division')->nullable();
        });

        try {
            DB::table('centros_de_costo')->insert([
                ['id_centro_costo' => 10, 'company_id' => '126', 'id_division' => 'DIV-A'],
                ['id_centro_costo' => 20, 'company_id' => '126-SUCURSAL', 'id_division' => 'DIV-B'],
                ['id_centro_costo' => 30, 'company_id' => '100', 'id_division' => 'DIV-C'],
            ]);

            foreach ([10, 20, 30] as $centroCosto) {
                DB::table('entradas_diario')->insert([
                    'external_key' => sha1('division-'.$centroCosto),
                    'no_asiento' => 'AS-'.$centroCosto,
                    'company_id' => $centroCosto === 30 ? '100' : '126',
                    'fecha' => '2026-07-05',
                    'cuenta' => '610101',
                    'id_centro_costo' => (string) $centroCosto,
                    'id_division' => 'ID-EXTERNO',
                    'debito' => $centroCosto,
                    'credito' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->withoutMiddleware()->getJson('/api-centros-costo/divisiones?empresa=126')
                ->assertOk()
                ->assertExactJson(['DIV-A', 'DIV-B']);

            $this->withoutMiddleware()->getJson('/api-centros-costo/divisiones?empresa=100')
                ->assertOk()
                ->assertExactJson(['DIV-C']);

            $this->withoutMiddleware()->getJson('/api-entradas-diario?empresa=126&fecha_inicio=2026-07-05&fecha_fin=2026-07-05&id_division=DIV-B')
                ->assertOk()
                ->assertJsonPath('summary.registros', 1)
                ->assertJsonPath('data.0.NoAsiento', 'AS-20');

            $this->withoutMiddleware()->getJson('/api-entradas-diario?empresa=126&fecha_inicio=2026-07-05&fecha_fin=2026-07-05&id_division=DIV-C')
                ->assertOk()
                ->assertJsonPath('summary.registros', 0);
        } finally {
            Schema::dropIfExists('centros_de_costo');
        }
    }

    public function test_data_endpoint_caps_requested_rows_to_keep_the_table_responsive(): void
    {
        $this->withoutMiddleware()
            ->getJson('/api-entradas-diario?empresa=126&fecha_inicio=2026-07-05&fecha_fin=2026-07-05&limit=50000')
            ->assertOk()
            ->assertJsonPath('limit', 500);
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

    public function test_sync_reports_the_external_authorization_failure(): void
    {
        $controller = new class extends Api
        {
            protected function fetchEntradasDiarioByDate(string $empresa, string $fecha): array
            {
                return [
                    'ok' => false,
                    'message' => 'El token configurado no tiene autorizacion para consultar entradas de diario. Contacta al administrador de ApiSJ.',
                    'status' => 502,
                    'external_status' => 401,
                    'items' => [],
                ];
            }
        };

        $this->app->instance(Api::class, $controller);

        $this->withoutMiddleware()
            ->postJson('/api-entradas-diario/sync', [
                'empresa' => '126',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-01',
            ])
            ->assertStatus(502)
            ->assertJsonPath('external_status', 401)
            ->assertJsonPath(
                'message',
                'El token configurado no tiene autorizacion para consultar entradas de diario. Contacta al administrador de ApiSJ.'
            );
    }

    public function test_sync_handles_array_values_in_optional_external_fields(): void
    {
        $controller = new class extends Api
        {
            protected function fetchEntradasDiarioByDate(string $empresa, string $fecha): array
            {
                return [
                    'ok' => true,
                    'items' => [[
                        'NOASIENTO' => 'AS-ARRAY',
                        'CUENTA' => '610101',
                        'FECHA' => $fecha,
                        'REF' => ['inesperado'],
                        'IDSOCIEDAD' => ['inesperado'],
                        'COMPANYID' => ['inesperado'],
                        'DEBITO' => ['inesperado'],
                    ]],
                ];
            }
        };

        $this->app->instance(Api::class, $controller);

        $this->withoutMiddleware()
            ->postJson('/api-entradas-diario/sync', [
                'empresa' => '126',
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2026-09-01',
            ])
            ->assertOk()
            ->assertJsonPath('creados', 1);

        $this->assertDatabaseHas('entradas_diario', [
            'no_asiento' => 'AS-ARRAY',
            'company_id' => '126',
            'ref' => null,
            'id_sociedad' => null,
        ]);
    }

    public function test_entradas_diario_uses_the_current_external_endpoint_and_iso_date(): void
    {
        $controller = new class extends Api
        {
            public function externalUrl(string $empresa, string $fecha): string
            {
                return $this->buildEntradasDiarioUrl($empresa, $fecha);
            }
        };

        $url = $controller->externalUrl('126', '2026-09-01');
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('/fe/ApiSJ/api/EntradasDiario', parse_url($url, PHP_URL_PATH));
        $this->assertSame('126', $query['intIdEmpresa']);
        $this->assertSame('2026-09-01', $query['dtFecha']);
    }
}
