<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IncentivosAdministrativosTest extends TestCase
{
    public function test_administrative_incentives_routes_are_registered(): void
    {
        $expectedRoutes = [
            'incentivos.calculo.index',
            'incentivos.calculo.reporte',
            'incentivos.calculo.periodo.guardar',
            'incentivos.calculo.calendario',
            'incentivos.calculo.calendario.guardar',
            'incentivos.calculo.calendario.terminales.reconocer',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing route [$routeName].");
        }
    }

    public function test_module_hub_exposes_the_administrative_incentives_view(): void
    {
        $module = collect(config('module_hubs.incentivos.items'))
            ->firstWhere('url', '/incentivos/calculo-de-incentivos');

        $this->assertNotNull($module);
        $this->assertSame('Cálculo de Incentivos', $module['nombre']);
        $this->assertTrue($module['activo']);
    }

    public function test_view_uses_the_administrative_name_and_routes(): void
    {
        $html = view('incentivos.incentivos-administrativos', [
            'coordinadores' => collect(),
            'administrativosConfig' => collect(),
            'terminalesExcluidasIncentivo' => collect(),
        ])->render();

        $this->assertStringContainsString('Cálculo de Incentivos', $html);
        $this->assertStringContainsString('/incentivos/calculo-de-incentivos/reporte?', $html);
        $this->assertStringContainsString('type="hidden" id="ni_alcance_productos" value="completo"', $html);
        $this->assertStringContainsString('id="modalAlcanceReporte"', $html);
        $this->assertStringContainsString('data-alcance="completo"', $html);
        $this->assertStringContainsString('data-alcance="no_tradicionales"', $html);
        $this->assertStringContainsString('Generar completo', $html);
        $this->assertStringContainsString('Generar no tradicional', $html);
        $this->assertStringContainsString("getElementById('modalAlcanceReporte')).show()", $html);
        $this->assertStringContainsString('button.dataset.alcance', $html);
        $this->assertStringContainsString('alcance_productos: alcanceProductos', $html);
        $this->assertStringNotContainsString('Calculo de Incentivos V6 - Pruebas', $html);
    }

    public function test_report_rejects_an_invalid_product_scope(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1]))
            ->withoutMiddleware()
            ->getJson(route('incentivos.calculo.reporte', [
                'fecha_ini' => '2099-01-01',
                'fecha_fin' => '2099-01-31',
                'alcance_productos' => 'privado_invalido',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('alcance_productos');
    }

    public function test_nontraditional_scope_uses_the_product_catalog(): void
    {
        $controller = app(\App\Http\Controllers\IncentivosController::class);
        $method = new \ReflectionMethod($controller, 'applyProductScope');
        $query = DB::table('vt_usuarios_bet');

        /** @var Builder $filteredQuery */
        $filteredQuery = $method->invoke($controller, $query, 'producto_id', 'no_tradicionales');

        $this->assertStringContainsString('CAST(producto_id AS SIGNED)', $filteredQuery->toSql());
        $this->assertStringContainsString('catalogo_juegos', $filteredQuery->toSql());
        $this->assertContains('No Tradicional', $filteredQuery->getBindings());
    }

    public function test_monthly_close_rejects_a_partial_product_report(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1]))
            ->withoutMiddleware()
            ->postJson(route('incentivos.calculo.periodo.guardar'), [
                'alcance_productos' => 'no_tradicionales',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('alcance_productos');
    }

    public function test_administrative_incentives_page_can_be_opened(): void
    {
        $this->withoutMiddleware()
            ->get(route('incentivos.calculo.index'))
            ->assertOk()
            ->assertSee('Cálculo de Incentivos');
    }

    public function test_previous_administrative_incentives_url_redirects_to_calculation_page(): void
    {
        $this->withoutMiddleware()
            ->get('/incentivos/incentivos-administrativos')
            ->assertRedirect('/incentivos/calculo-de-incentivos');
    }

    public function test_calendar_endpoint_returns_its_expected_structure(): void
    {
        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('terminal')->nullable();
            $table->string('sistema')->nullable();
            $table->string('empresa')->nullable();
            $table->string('nombre_agencia')->nullable();
            $table->string('agencia')->nullable();
            $table->boolean('estatus')->default(true);
        });
        Schema::create('incentivo_terminal_tipo_pagos', function (Blueprint $table): void {
            $table->id();
            $table->string('sistema');
            $table->string('terminal');
            $table->date('fecha');
            $table->string('tipo_pago')->nullable();
            $table->timestamps();
        });

        $this->actingAs(User::factory()->make(['id' => 1]))
            ->withoutMiddleware()
            ->getJson(route('incentivos.calculo.calendario', [
                'fecha_ini' => '2099-01-01',
                'fecha_fin' => '2099-01-01',
                'sistema' => 'Todos',
                'per_page' => 25,
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'fechas',
                'terminales',
                'resumen' => ['terminales', 'configuraciones'],
                'paginacion' => ['pagina_actual', 'ultima_pagina', 'por_pagina', 'total', 'desde', 'hasta'],
            ]);
    }
}
