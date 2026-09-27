<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgenciaSinActividadReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('nombre')->nullable();
            $table->string('terminal')->nullable();
            $table->string('grupo')->nullable();
            $table->string('central')->nullable();
            $table->string('gerente_de_servicio')->nullable();
            $table->unsignedTinyInteger('estatus')->default(1);
            $table->timestamps();
        });
        Schema::create('ventas_usuarios_bet', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia_id');
            $table->decimal('monto', 14, 2)->default(0);
            $table->date('fecha');
            $table->timestamps();
        });
        Schema::create('agencia_sin_actividad_motivos', function (Blueprint $table): void {
            $table->id();
            $table->string('sistema');
            $table->string('terminal');
            $table->string('motivo');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->timestamps();
            $table->unique(['sistema', 'terminal']);
        });
        Schema::create('agencia_sin_actividad_configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->decimal('porcentaje_minimo', 5, 2)->default(90);
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('agencia_sin_actividad_configuraciones');
        Schema::dropIfExists('agencia_sin_actividad_motivos');
        Schema::dropIfExists('ventas_usuarios_bet');
        Schema::dropIfExists('agencias');

        parent::tearDown();
    }

    public function test_routes_view_and_human_resources_card_are_registered(): void
    {
        $this->assertTrue(Route::has('recursos-humanos.agencias-sin-actividad.index'));
        $this->assertTrue(Route::has('recursos-humanos.agencias-sin-actividad.data'));
        $this->assertTrue(Route::has('recursos-humanos.agencias-sin-actividad.options'));
        $this->assertTrue(Route::has('recursos-humanos.agencias-sin-actividad.motivo.store'));
        $this->assertTrue(Route::has('recursos-humanos.agencias-sin-actividad.configuration'));
        $this->assertTrue(Route::has('recursos-humanos.agencias-sin-actividad.configuration.update'));

        $view = file_get_contents(resource_path('views/recursos_humanos/agencias-sin-actividad.blade.php'));
        $config = file_get_contents(config_path('recursos_humanos.php'));
        $service = file_get_contents(app_path('Services/AgenciaSinActividadReportService.php'));

        $this->assertIsString($view);
        $this->assertIsString($config);
        $this->assertIsString($service);
        $this->assertStringContainsString('Agencias sin actividad', $view);
        $this->assertStringContainsString('graficoActividad', $view);
        $this->assertStringContainsString('graficoCentrales', $view);
        $this->assertStringContainsString('stacked: false', $view);
        $this->assertStringContainsString('Cantidad de terminales', $view);
        $this->assertStringContainsString('id="btnAbrirConfiguracion"', $view);
        $this->assertStringContainsString('id="porcentajeMinimo"', $view);
        $this->assertStringContainsString('item.cumplimiento) < minimumCompliance', $view);
        $this->assertStringContainsString('function visibleSummary(data)', $view);
        $this->assertStringContainsString('renderSummary(currentReport)', $view);
        $this->assertStringContainsString('id="tarjetaMotivos"', $view);
        $this->assertStringContainsString('id="tablaTerminalesMotivo"', $view);
        $this->assertStringContainsString('Gerente de servicio', $view);
        $this->assertStringContainsString('id="btnModoPantalla"', $view);
        $this->assertStringContainsString('requestFullscreen()', $view);
        $this->assertStringContainsString('tablaAgenciasActividad', $view);
        $this->assertStringContainsString('VtUsuarioBet::query()', $service);
        $this->assertStringContainsString('VtUsuarioNet::query()', $service);
        $this->assertStringContainsString('VentasDelta::query()', $service);
        $this->assertStringContainsString('VentaDsVirtual::query()', $service);
        $this->assertStringContainsString('Promise.all([loadOptions(), loadConfiguration()]);', $view);
        $this->assertStringNotContainsString('loadOptions().then(loadReport)', $view);
        $this->assertStringContainsString('/recursos-humanos/agencias-sin-actividad', $config);
    }

    public function test_report_counts_only_active_agencies_and_detects_missing_or_zero_sales(): void
    {
        DB::table('agencias')->insert([
            ['codigo' => 'A1', 'nombre' => 'Con ventas', 'terminal' => '00101', 'grupo' => 'G1', 'central' => 'Norte', 'gerente_de_servicio' => 'Ana', 'estatus' => 1],
            ['codigo' => 'A2', 'nombre' => 'Venta cero', 'terminal' => '00102', 'grupo' => 'G1', 'central' => 'Norte', 'gerente_de_servicio' => 'Ana', 'estatus' => 1],
            ['codigo' => 'A3', 'nombre' => 'Sin registro', 'terminal' => '00103', 'grupo' => 'G2', 'central' => 'Sur', 'gerente_de_servicio' => 'Luis', 'estatus' => 1],
            ['codigo' => 'A4', 'nombre' => 'Inactiva', 'terminal' => '00104', 'grupo' => 'G2', 'central' => 'Sur', 'gerente_de_servicio' => 'Luis', 'estatus' => 0],
        ]);
        DB::table('ventas_usuarios_bet')->insert([
            ['agencia_id' => '101', 'monto' => 1500, 'fecha' => '2026-09-26'],
            ['agencia_id' => '102', 'monto' => 0, 'fecha' => '2026-09-26'],
        ]);

        $this->withoutMiddleware()
            ->getJson(route('recursos-humanos.agencias-sin-actividad.data', [
                'sistema' => 'lotobet',
                'fecha_inicio' => '2026-09-26',
                'fecha_fin' => '2026-09-26',
            ]))
            ->assertOk()
            ->assertJsonPath('resumen.total', 3)
            ->assertJsonPath('resumen.con_ventas', 1)
            ->assertJsonPath('resumen.sin_ventas', 2)
            ->assertJsonPath('resumen.porcentaje_actividad', 33.33)
            ->assertJsonPath('por_central.0.cumplimiento', 0)
            ->assertJsonCount(3, 'agencias');
    }

    public function test_reason_can_be_saved_for_an_agency(): void
    {
        $this->withoutMiddleware()
            ->postJson(route('recursos-humanos.agencias-sin-actividad.motivo.store'), [
                'sistema' => 'lotobet',
                'terminal' => '00103',
                'motivo' => 'Sin internet',
                'observacion' => 'Pendiente de visita.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Motivo guardado correctamente.');

        $this->assertDatabaseHas('agencia_sin_actividad_motivos', [
            'sistema' => 'lotobet',
            'terminal' => '00103',
            'motivo' => 'Sin internet',
        ]);
    }

    public function test_chart_compliance_threshold_can_be_saved_and_retrieved(): void
    {
        $this->withoutMiddleware()
            ->putJson(route('recursos-humanos.agencias-sin-actividad.configuration.update'), [
                'porcentaje_minimo' => 87.5,
            ])
            ->assertOk()
            ->assertJsonPath('porcentaje_minimo', 87.5);

        $this->withoutMiddleware()
            ->getJson(route('recursos-humanos.agencias-sin-actividad.configuration'))
            ->assertOk()
            ->assertJsonPath('porcentaje_minimo', 87.5);

        $this->assertDatabaseHas('agencia_sin_actividad_configuraciones', [
            'porcentaje_minimo' => 87.5,
        ]);
    }
}
