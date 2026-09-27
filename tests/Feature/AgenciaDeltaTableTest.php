<?php

namespace Tests\Feature;

use App\Exports\AgenciasDeltaExport;
use App\Http\Controllers\AgenciaDeltaController;
use App\Models\AgenciaDelta;
use App\Models\AgenciaLotedom;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgenciaDeltaTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('agencias_delta');
        Schema::dropIfExists('agencias_lotedom');
        Schema::create('agencias_lotedom', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia', 25)->nullable();
            $table->string('codigo', 25)->nullable();
            $table->string('nombre_agencia', 55)->nullable();
            $table->string('nombre', 55)->nullable();
            $table->string('terminal', 25)->nullable()->unique();
            $table->string('horario_am', 35)->nullable();
            $table->string('horario_pm', 35)->nullable();
            $table->string('sistema', 55)->nullable();
            $table->string('empresa', 60)->nullable();
            $table->string('ciudad', 55)->nullable();
            $table->string('ruta', 55)->nullable();
            $table->string('operador', 55)->nullable();
            $table->string('coordinador', 55)->nullable();
            $table->tinyInteger('estatus')->default(1);
            $table->boolean('aplica_incentivo')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('agencias_delta');
        Schema::dropIfExists('agencias_lotedom');

        parent::tearDown();
    }

    private function runCreateAgenciasDeltaMigration(): void
    {
        $migration = require database_path('migrations/2026_09_25_194240_create_agencias_delta_table.php');
        $migration->up();
    }

    private function seedCatalogoLotedom(): void
    {
        AgenciaLotedom::query()->insert([
            ['agencia' => 'L1', 'terminal' => '1001', 'sistema' => 'lotedom', 'empresa' => 'Lotedom', 'estatus' => 1],
            ['agencia' => 'D1', 'terminal' => '2001', 'sistema' => 'delta', 'empresa' => null, 'estatus' => 1],
            ['agencia' => 'D2', 'terminal' => '2002', 'sistema' => null, 'empresa' => 'Delta SRL', 'estatus' => 0],
        ]);
    }

    public function test_la_migration_crea_la_tabla_con_las_columnas_nuevas_de_75_caracteres(): void
    {
        $this->runCreateAgenciasDeltaMigration();

        foreach (['grupo', 'central', 'gerente_de_servicio', 'tipo_pago'] as $columna) {
            $this->assertTrue(Schema::hasColumn('agencias_delta', $columna), "Falta la columna {$columna}");
        }

        $agencia = AgenciaDelta::factory()->create([
            'grupo' => 'Grupo A',
            'central' => 'Central 1',
            'gerente_de_servicio' => 'Gerente Uno',
            'tipo_pago' => 'Semanal',
        ]);

        $this->assertSame('Grupo A', $agencia->fresh()->grupo);
        $this->assertSame('Semanal', $agencia->fresh()->tipo_pago);
    }

    public function test_la_migration_copia_solo_las_filas_delta_y_no_toca_lotedom(): void
    {
        $this->seedCatalogoLotedom();

        $this->runCreateAgenciasDeltaMigration();

        $this->assertEqualsCanonicalizing(['2001', '2002'], AgenciaDelta::query()->pluck('terminal')->all());
        $this->assertEquals(0, AgenciaDelta::query()->where('terminal', '2002')->value('estatus'));
        $this->assertSame(3, AgenciaLotedom::query()->count());
        $this->assertFalse(Schema::hasColumn('agencias_lotedom', 'grupo'));
    }

    public function test_la_migration_es_idempotente_si_se_ejecuta_dos_veces(): void
    {
        $this->seedCatalogoLotedom();

        $this->runCreateAgenciasDeltaMigration();
        $this->runCreateAgenciasDeltaMigration();

        $this->assertSame(2, AgenciaDelta::query()->count());
    }

    public function test_el_listado_delta_lee_solo_de_agencias_delta(): void
    {
        $this->runCreateAgenciasDeltaMigration();
        AgenciaDelta::factory()->create(['agencia' => 'SOLO-DELTA', 'terminal' => '3001']);
        AgenciaLotedom::query()->insert([
            ['agencia' => 'SOLO-LOTEDOM', 'terminal' => '4001', 'sistema' => 'delta', 'empresa' => 'Delta', 'estatus' => 1],
        ]);

        $payload = app(AgenciaDeltaController::class)
            ->list(Request::create('/agencias-delta-list', 'GET', ['empresa_filter' => 'lotedom']))
            ->getData(true);

        $this->assertSame(1, $payload['recordsTotal']);
        $this->assertSame(['SOLO-DELTA'], array_column($payload['data'], 'agencia'));
    }

    public function test_crear_en_delta_no_inserta_en_lotedom(): void
    {
        $this->runCreateAgenciasDeltaMigration();

        AgenciaDelta::factory()->count(2)->create();

        $this->assertSame(2, DB::table('agencias_delta')->count());
        $this->assertSame(0, DB::table('agencias_lotedom')->count());
    }

    public function test_el_listado_y_los_formularios_exponen_las_columnas_nuevas(): void
    {
        $this->runCreateAgenciasDeltaMigration();
        $agencia = AgenciaDelta::factory()->create([
            'grupo' => 'Grupo Norte',
            'central' => 'Central Uno',
            'gerente_de_servicio' => 'Gerente Prueba',
            'tipo_pago' => 'Semanal',
        ]);

        $payload = app(AgenciaDeltaController::class)
            ->list(Request::create('/agencias-delta-list', 'GET'))
            ->getData(true);

        $this->assertSame('Grupo Norte', $payload['data'][0]['grupo']);
        $this->assertSame('Central Uno', $payload['data'][0]['central']);
        $this->assertSame('Gerente Prueba', $payload['data'][0]['gerente_de_servicio']);
        $this->assertSame('Semanal', $payload['data'][0]['tipo_pago']);

        $createHtml = file_get_contents(resource_path('views/agencias-delta/create.blade.php'));
        $editHtml = file_get_contents(resource_path('views/agencias-delta/edit.blade.php'));

        foreach (['grupo', 'central', 'gerente_de_servicio', 'tipo_pago'] as $campo) {
            $this->assertStringContainsString('name="'.$campo.'"', $createHtml);
            $this->assertStringContainsString('name="'.$campo.'"', $editHtml);
        }

        $this->assertContains('Grupo', (new AgenciasDeltaExport)->headings());
        $this->assertContains('Tipo de Pago', (new AgenciasDeltaExport)->headings());
    }
}
