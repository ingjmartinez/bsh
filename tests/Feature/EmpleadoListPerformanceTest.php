<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmpleadoListPerformanceTest extends TestCase
{
    public function test_dashboard_places_workforce_charts_before_salary_charts(): void
    {
        $view = file_get_contents(resource_path('views/empleado/index.blade.php'));

        $this->assertNotFalse($view);
        $this->assertLessThan(strpos($view, 'chartSalarioCiudad" class="rrhh-chart"'), strpos($view, 'chartEmpleadosCiudad" class="rrhh-chart"'));
        $this->assertStringContainsString("colors: ['#79b9a7', '#e3a59c']", $view);
        $this->assertMatchesRegularExpression('/Resumen por ciudad.*?<div class="rrhh-section-label mb-2">Detalle de empleados<\/div>\s*<div class="row g-3">\s*<div class="col-12">\s*<div class="card rrhh-card">/s', $view);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('centros_de_costo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('id_centro_costo')->unique();
            $table->string('descripcion');
            $table->string('id_division')->nullable();
        });

        DB::table('centros_de_costo')->insert([
            ['id_centro_costo' => 10, 'descripcion' => 'Operaciones', 'id_division' => 'DIV-A'],
            ['id_centro_costo' => 20, 'descripcion' => 'Administración', 'id_division' => 'DIV-B'],
        ]);

        Schema::create('empleados', function (Blueprint $table): void {
            $table->id();
            $table->string('companyid');
            $table->string('empleadoid');
            $table->integer('idcentrocosto')->nullable();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('cedula')->nullable();
            $table->string('ciudad')->nullable();
            $table->decimal('salario', 12, 2)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_egreso')->nullable();
        });

        foreach (range(1, 25) as $index) {
            DB::table('empleados')->insert([
                'companyid' => $index <= 20 ? '126' : '100',
                'empleadoid' => str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'idcentrocosto' => 10,
                'nombres' => $index === 15 ? 'Nombre Especial' : 'Empleado '.$index,
                'apellidos' => 'Prueba',
                'cedula' => '001'.str_pad((string) $index, 8, '0', STR_PAD_LEFT),
                'ciudad' => 'Santo Domingo',
                'salario' => 30000 + $index,
                'fecha_ingreso' => '2026-01-01',
            ]);
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('centros_de_costo');

        parent::tearDown();
    }

    public function test_list_uses_server_side_pagination_and_company_filter(): void
    {
        $response = $this->withoutMiddleware()->getJson('/empleados/list?draw=3&start=0&length=10&empresa=126');

        $response
            ->assertOk()
            ->assertJsonPath('draw', 3)
            ->assertJsonPath('recordsTotal', 20)
            ->assertJsonPath('recordsFiltered', 20)
            ->assertJsonCount(10, 'data');
    }

    public function test_division_table_filters_employees_by_their_cost_center(): void
    {
        DB::table('empleados')->where('empleadoid', '0025')->update(['idcentrocosto' => 20]);

        $this->withoutMiddleware()->getJson('/empleados/divisiones')
            ->assertOk()
            ->assertExactJson(['DIV-A', 'DIV-B']);

        $this->withoutMiddleware()->getJson('/empleados/list-division?draw=2&start=0&length=10&division=DIV-B')
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.empleadoid', 25)
            ->assertJsonPath('data.0.centro_costo', 'Administración')
            ->assertJsonPath('data.0.division', 'DIV-B');
    }

    public function test_list_searches_before_paginating(): void
    {
        $response = $this->withoutMiddleware()->getJson('/empleados/list?draw=1&start=0&length=10&search[value]=Especial');

        $response
            ->assertOk()
            ->assertJsonPath('recordsTotal', 25)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombres', 'Nombre Especial');
    }

    public function test_unfiltered_list_only_counts_employees_once(): void
    {
        DB::enableQueryLog();

        try {
            $this->withoutMiddleware()->getJson('/empleados/list?draw=1&start=0&length=10&empresa=126')->assertOk();
            $countQueries = array_filter(DB::getQueryLog(), fn (array $query): bool => str_contains(strtolower($query['query']), 'count(*)'));

            $this->assertCount(1, $countQueries);
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    public function test_employee_view_loads_dashboard_and_table_without_sequential_waits(): void
    {
        $html = view('empleado.index')->render();

        $this->assertStringContainsString('serverSide: true', $html);
        $this->assertStringContainsString('deferRender: true', $html);
        $this->assertStringContainsString('Promise.all([cargarDashboard(), list(), listPorDivision()])', $html);
        $this->assertStringContainsString('dashboardRequest.abort()', $html);
        $this->assertStringContainsString('textoSeguro(fila.ciudad', $html);
        $this->assertStringNotContainsString('data.forEach(item =>', $html);
    }
}
