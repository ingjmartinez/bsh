<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NuevoIngresoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-28 10:00:00');

        Schema::create('empleados', function (Blueprint $table): void {
            $table->id();
            $table->string('cedula');
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_ingreso')->nullable();
            $table->boolean('estatus')->default(true);
        });
        Schema::create('nuevo_ingreso_configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('dias_habiles')->default(20);
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();
        });
        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('terminal');
            $table->string('grupo')->nullable();
            $table->string('central')->nullable();
            $table->string('gerente_de_servicio')->nullable();
            $table->boolean('estatus')->default(true);
        });
        Schema::create('ventas_usuarios_bet', function (Blueprint $table): void {
            $table->id();
            $table->string('cedula');
            $table->string('agencia_id');
            $table->decimal('monto', 18, 2)->default(0);
            $table->date('fecha');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('ventas_usuarios_bet');
        Schema::dropIfExists('agencias');
        Schema::dropIfExists('nuevo_ingreso_configuraciones');
        Schema::dropIfExists('empleados');
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_routes_card_and_view_are_registered(): void
    {
        $this->assertTrue(Route::has('recursos-humanos.nuevos-ingresos.index'));
        $this->assertTrue(Route::has('recursos-humanos.nuevos-ingresos.data'));
        $this->assertTrue(Route::has('recursos-humanos.nuevos-ingresos.configuration'));
        $this->assertTrue(Route::has('recursos-humanos.nuevos-ingresos.configuration.update'));

        $config = file_get_contents(config_path('recursos_humanos.php'));
        $view = file_get_contents(resource_path('views/recursos_humanos/nuevos-ingresos.blade.php'));
        $this->assertIsString($config);
        $this->assertIsString($view);
        $this->assertStringContainsString('/recursos-humanos/nuevos-ingresos', $config);
        $this->assertStringContainsString('tablaNuevosIngresos', $view);
        $this->assertStringContainsString('id="criterioDiasHabiles"', $view);
        $this->assertStringContainsString('id="btnConfigurarNuevosIngresos"', $view);
        $this->assertStringContainsString('id="limiteDiasHabiles"', $view);
        $this->assertStringContainsString('id="filtroGrupoNuevoIngreso"', $view);
        $this->assertStringContainsString('id="filtroCentralNuevoIngreso"', $view);
        $this->assertStringContainsString('id="filtroGerenteNuevoIngreso"', $view);
        $controller = file_get_contents(app_path('Http/Controllers/NuevoIngresoController.php'));
        $this->assertIsString($controller);
        $this->assertStringContainsString('CANDIDATE_CALENDAR_DAYS = 35', $controller);
    }

    public function test_only_active_employees_with_less_than_twenty_business_days_are_returned(): void
    {
        DB::table('empleados')->insert([
            ['cedula' => '001', 'nombres' => 'Ingreso', 'apellidos' => 'Hoy', 'fecha_ingreso' => today()->toDateString(), 'estatus' => 1],
            ['cedula' => '002', 'nombres' => 'Ingreso', 'apellidos' => 'Reciente', 'fecha_ingreso' => today()->subWeekdays(19)->toDateString(), 'estatus' => 1],
            ['cedula' => '003', 'nombres' => 'Ingreso', 'apellidos' => 'Antiguo', 'fecha_ingreso' => today()->subWeekdays(20)->toDateString(), 'estatus' => 1],
            ['cedula' => '004', 'nombres' => 'Ingreso', 'apellidos' => 'Inactivo', 'fecha_ingreso' => today()->subWeekdays(5)->toDateString(), 'estatus' => 0],
            ['cedula' => '005', 'nombres' => 'Ingreso', 'apellidos' => 'Futuro', 'fecha_ingreso' => today()->addDay()->toDateString(), 'estatus' => 1],
        ]);

        $response = $this->withoutMiddleware()
            ->getJson(route('recursos-humanos.nuevos-ingresos.data'))
            ->assertOk()
            ->assertJsonPath('limite_dias_habiles', 20)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(2, 'empleados');

        $this->assertSame(['001', '002'], collect($response->json('empleados'))->pluck('cedula')->sort()->values()->all());
    }

    public function test_business_day_limit_can_be_reduced_and_is_persisted(): void
    {
        DB::table('empleados')->insert([
            ['cedula' => '001', 'nombres' => 'Ingreso', 'apellidos' => 'Hoy', 'fecha_ingreso' => today()->toDateString(), 'estatus' => 1],
            ['cedula' => '002', 'nombres' => 'Ingreso', 'apellidos' => 'Once días', 'fecha_ingreso' => today()->subWeekdays(11)->toDateString(), 'estatus' => 1],
        ]);

        $this->withoutMiddleware()
            ->putJson(route('recursos-humanos.nuevos-ingresos.configuration.update'), ['dias_habiles' => 10])
            ->assertOk()
            ->assertJsonPath('dias_habiles', 10);

        $this->withoutMiddleware()
            ->getJson(route('recursos-humanos.nuevos-ingresos.data'))
            ->assertOk()
            ->assertJsonPath('limite_dias_habiles', 10)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('empleados.0.cedula', '001');

        $this->assertDatabaseHas('nuevo_ingreso_configuraciones', ['dias_habiles' => 10]);
    }

    public function test_business_day_limit_above_twenty_returns_spanish_validation_message(): void
    {
        $this->withoutMiddleware()
            ->putJson(route('recursos-humanos.nuevos-ingresos.configuration.update'), ['dias_habiles' => 25])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['dias_habiles'])
            ->assertJsonPath('errors.dias_habiles.0', 'El límite máximo permitido es 20 días hábiles.');
    }

    public function test_group_central_and_manager_filter_uses_most_recent_sales_agency(): void
    {
        DB::table('empleados')->insert([
            ['cedula' => '001', 'nombres' => 'Ana', 'apellidos' => 'Uno', 'fecha_ingreso' => today()->subWeekdays(5)->toDateString(), 'estatus' => 1],
            ['cedula' => '002', 'nombres' => 'Beto', 'apellidos' => 'Dos', 'fecha_ingreso' => today()->subWeekdays(5)->toDateString(), 'estatus' => 1],
            ['cedula' => '003', 'nombres' => 'Cira', 'apellidos' => 'Tres', 'fecha_ingreso' => today()->subWeekdays(5)->toDateString(), 'estatus' => 1],
        ]);
        DB::table('agencias')->insert([
            ['terminal' => '0010', 'grupo' => 'Grupo A', 'central' => 'Central A', 'gerente_de_servicio' => 'Gerente A', 'estatus' => 1],
            ['terminal' => '20', 'grupo' => 'Grupo B', 'central' => 'Central B', 'gerente_de_servicio' => 'Gerente B', 'estatus' => 1],
        ]);
        DB::table('ventas_usuarios_bet')->insert([
            ['cedula' => '001', 'agencia_id' => '10', 'fecha' => today()->subDays(2)->toDateString()],
            ['cedula' => '001', 'agencia_id' => '20', 'fecha' => today()->subDay()->toDateString()],
            ['cedula' => '002', 'agencia_id' => '10', 'fecha' => today()->subDay()->toDateString()],
        ]);

        $this->withoutMiddleware()->getJson(route('recursos-humanos.nuevos-ingresos.data', [
            'grupo' => 'Grupo A', 'central' => 'Central A', 'gerente' => 'Gerente A',
        ]))->assertOk()->assertJsonPath('total', 1)->assertJsonPath('empleados.0.cedula', '002');

        $this->withoutMiddleware()->getJson(route('recursos-humanos.nuevos-ingresos.data', [
            'grupo' => 'Grupo B',
        ]))->assertOk()->assertJsonPath('total', 1)->assertJsonPath('empleados.0.cedula', '001');
    }

    public function test_invalid_filter_is_rejected(): void
    {
        $this->withoutMiddleware()->getJson(route('recursos-humanos.nuevos-ingresos.data', [
            'grupo' => str_repeat('x', 192),
        ]))->assertUnprocessable()->assertJsonValidationErrors('grupo');
    }
}
