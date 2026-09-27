<?php

namespace Tests\Feature;

use App\Exports\AgenciasExport;
use App\Exports\AgenciasLotedomExport;
use App\Http\Controllers\AgenciaController;
use App\Http\Controllers\AgenciaDeltaController;
use App\Http\Controllers\AgenciaLotedomController;
use App\Models\Agencia;
use App\Models\AgenciaLotedom;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgenciaOperationalColumnsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('agencias_lotedom');
        Schema::dropIfExists('agencias');

        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 25)->nullable();
            $table->string('terminal', 25)->nullable();
            $table->string('nombre', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('agencias_lotedom', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia', 25)->nullable();
            $table->string('terminal', 25)->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('agencias_lotedom');
        Schema::dropIfExists('agencias');

        parent::tearDown();
    }

    public function test_las_columnas_operativas_funcionan_en_agencias_y_lotedom(): void
    {
        $migration = require database_path('migrations/2026_09_25_220246_add_operational_columns_to_agencias_and_agencias_lotedom_tables.php');
        $migration->up();

        foreach (['agencias', 'agencias_lotedom'] as $table) {
            foreach (['grupo', 'central', 'gerente_de_servicio', 'tipo_pago'] as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column));
            }
        }

        $values = [
            'grupo' => 'Grupo Norte',
            'central' => 'Central Uno',
            'gerente_de_servicio' => 'Gerente Uno',
            'tipo_pago' => 'Semanal',
        ];

        $agencia = Agencia::query()->create(['codigo' => '100', ...$values]);
        $agenciaLotedom = AgenciaLotedom::query()->create(['agencia' => '200', ...$values]);

        $this->assertSame('Central Uno', $agencia->fresh()->central);
        $this->assertSame('Semanal', $agenciaLotedom->fresh()->tipo_pago);
    }

    public function test_las_vistas_y_exportaciones_incluyen_las_columnas_operativas(): void
    {
        foreach (['agencias', 'agencias-lotedom'] as $directory) {
            foreach (['index', 'create', 'edit'] as $view) {
                $contents = file_get_contents(resource_path("views/{$directory}/{$view}.blade.php"));

                foreach (['grupo', 'central', 'gerente_de_servicio', 'tipo_pago'] as $column) {
                    $this->assertStringContainsString($column, $contents);
                }
            }
        }

        foreach ([new AgenciasExport, new AgenciasLotedomExport] as $export) {
            $this->assertContains('Grupo', $export->headings());
            $this->assertContains('Central', $export->headings());
            $this->assertContains('Gerente de Servicio', $export->headings());
            $this->assertContains('Tipo de Pago', $export->headings());
        }
    }

    public function test_la_actualizacion_masiva_normaliza_lotobet_real_y_conserva_las_columnas_nuevas(): void
    {
        $method = new \ReflectionMethod(AgenciaController::class, 'extraerCamposParaActualizacionMasiva');

        $updates = $method->invoke(app(AgenciaController::class), [
            'terminal' => '07010069',
            'sistema' => 'Lotobet Real',
            'grupo' => 'Grupo Wendy Ventura',
            'central' => 'Vilorio',
            'gerente_de_servicio' => 'Eric Vilorio',
            'tipo_pago' => '60-08-04 P-1000 T-20116',
        ]);

        $this->assertSame('lotobet', $updates['sistema']);
        $this->assertSame('Grupo Wendy Ventura', $updates['grupo']);
        $this->assertSame('Vilorio', $updates['central']);
        $this->assertSame('Eric Vilorio', $updates['gerente_de_servicio']);
        $this->assertSame('60-08-04 P-1000 T-20116', $updates['tipo_pago']);
    }

    public function test_delta_y_lotedom_normalizan_el_sistema_y_conservan_las_columnas_nuevas(): void
    {
        $cases = [
            [AgenciaDeltaController::class, 'Lotobet Delta', 'delta'],
            [AgenciaLotedomController::class, 'Lote Dom', 'lotedom'],
        ];

        foreach ($cases as [$controller, $systemValue, $expectedSystem]) {
            $method = new \ReflectionMethod($controller, 'extraerCamposParaActualizacionMasiva');
            $updates = $method->invoke(app($controller), [
                'terminal' => '10001',
                'sistema' => $systemValue,
                'grupo' => 'Grupo Norte',
                'central' => 'Central Uno',
                'gerente_de_servicio' => 'Gerente Uno',
                'tipo_pago' => 'Semanal',
            ]);

            $this->assertSame($expectedSystem, $updates['sistema']);
            $this->assertSame('Grupo Norte', $updates['grupo']);
            $this->assertSame('Central Uno', $updates['central']);
            $this->assertSame('Gerente Uno', $updates['gerente_de_servicio']);
            $this->assertSame('Semanal', $updates['tipo_pago']);
        }
    }
}
