<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmpleadoListPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_employee_view_loads_dashboard_and_table_without_sequential_waits(): void
    {
        $html = view('empleado.index')->render();

        $this->assertStringContainsString('serverSide: true', $html);
        $this->assertStringContainsString('deferRender: true', $html);
        $this->assertStringContainsString('Promise.all([cargarDashboard(), list()])', $html);
        $this->assertStringNotContainsString('data.forEach(item =>', $html);
    }
}
