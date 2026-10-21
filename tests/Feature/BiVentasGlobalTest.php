<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BiVentasGlobalTest extends TestCase
{
    private const TABLAS = [
        'agencias', 'agencias_lotedom', 'agencias_delta', 'ventas_usuarios_bet', 'ventas_usuarios_net',
        'ventas_flash_bet', 'ventas_ds_virtual', 'premios_bet', 'premios_net', 'catalogo_juegos',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->dropTablas();

        foreach (['agencias', 'agencias_lotedom', 'agencias_delta'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
                $table->string('terminal', 25)->nullable();
                $table->tinyInteger('estatus')->default(1);
                $table->string('grupo', 75)->nullable();
                $table->string('central', 75)->nullable();
                $table->string('gerente_de_servicio', 75)->nullable();
                $table->string('tipo_pago', 75)->nullable();
            });
        }

        foreach (['ventas_usuarios_bet', 'ventas_usuarios_net'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
                $table->string('agencia_id')->nullable();
                $table->bigInteger('producto_id')->nullable();
                $table->string('descripcion')->nullable();
                $table->string('tipo')->nullable();
                $table->decimal('monto', 14, 2)->default(0);
                $table->date('fecha');
            });
        }

        foreach (['premios_bet', 'premios_net'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
                $table->string('agencia_id')->nullable();
                $table->decimal('monto', 14, 2)->default(0);
                $table->date('fecha');
            });
        }

        Schema::create('ventas_ds_virtual', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia_id')->nullable();
            $table->decimal('ventas', 14, 2)->default(0);
            $table->decimal('premios', 14, 2)->default(0);
            $table->date('fecha');
        });
        Schema::create('catalogo_juegos', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('producto_id')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('tipo')->nullable();
        });
        Schema::create('ventas_flash_bet', function (Blueprint $table): void {
            $table->id();
            $table->integer('numero_externo')->nullable();
            $table->decimal('venta_loteria', 14, 2)->default(0);
            $table->decimal('premios_pagado', 14, 2)->default(0);
            $table->decimal('venta_recarga', 14, 2)->default(0);
            $table->decimal('ventas_no_tradicional', 14, 2)->default(0);
            $table->decimal('premios_pagados_no_tradicional', 14, 2)->default(0);
            $table->date('fecha');
        });
    }

    protected function tearDown(): void
    {
        $this->dropTablas();

        parent::tearDown();
    }

    private function dropTablas(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }

    private function seedPlataformas(): void
    {
        DB::table('agencias')->insert([['terminal' => '1', 'estatus' => 1, 'grupo' => 'G1']]);
        DB::table('agencias_lotedom')->insert([['terminal' => '100', 'estatus' => 1, 'grupo' => 'G1'], ['terminal' => '101', 'estatus' => 0, 'grupo' => 'G2']]);
        DB::table('agencias_delta')->insert([['terminal' => '555', 'estatus' => 1, 'grupo' => 'G3']]);

        DB::table('ventas_usuarios_bet')->insert([
            ['agencia_id' => '1', 'producto_id' => 1, 'tipo' => 'Tradicional', 'monto' => 2000, 'fecha' => '2026-10-05'],
            ['agencia_id' => '1', 'producto_id' => 1, 'tipo' => 'Tradicional', 'monto' => 1000, 'fecha' => '2026-09-28'],
        ]);
        DB::table('premios_bet')->insert([['agencia_id' => '1', 'monto' => 500, 'fecha' => '2026-10-05']]);

        DB::table('ventas_usuarios_net')->insert([
            ['agencia_id' => '100', 'producto_id' => 1, 'tipo' => 'boleto', 'monto' => 1000, 'fecha' => '2026-10-05'],
        ]);
        DB::table('premios_net')->insert([['agencia_id' => '100', 'monto' => 300, 'fecha' => '2026-10-05']]);

        DB::table('ventas_flash_bet')->insert([
            ['numero_externo' => 555, 'venta_loteria' => 800, 'premios_pagado' => 150, 'venta_recarga' => 200, 'ventas_no_tradicional' => 100, 'premios_pagados_no_tradicional' => 50, 'fecha' => '2026-10-05'],
        ]);
    }

    public function test_global_dashboard_consolidates_the_three_platforms(): void
    {
        $this->seedPlataformas();

        $this->withoutMiddleware()
            ->getJson(route('bi.ventas-global.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-05']))
            ->assertOk()
            ->assertJsonPath('terminales.total', 4)
            ->assertJsonPath('terminales.activas', 3)
            ->assertJsonPath('terminales.inactivas', 1)
            ->assertJsonPath('terminales.vendieron', 3)
            ->assertJsonPath('venta_global.total', 4100)
            ->assertJsonPath('venta_global.variacion', 310)
            ->assertJsonPath('premios.total', 1000)
            ->assertJsonPath('resultado_bruto.total', 3100)
            ->assertJsonCount(3, 'plataformas')
            ->assertJsonPath('plataformas.0.key', 'real')
            ->assertJsonPath('plataformas.0.venta', 2000)
            ->assertJsonPath('plataformas.1.key', 'lotodom')
            ->assertJsonPath('plataformas.1.venta', 1000)
            ->assertJsonPath('plataformas.2.key', 'delta')
            ->assertJsonPath('plataformas.2.venta', 1100)
            ->assertJsonCount(7, 'grafico')
            ->assertJsonPath('grafico.6.real', 2000)
            ->assertJsonPath('grafico.6.lotodom', 1000)
            ->assertJsonPath('grafico.6.delta', 1100)
            ->assertJsonPath('grafico.6.total', 4100);
    }

    public function test_global_dashboard_can_be_limited_to_a_single_system(): void
    {
        $this->seedPlataformas();

        $this->withoutMiddleware()
            ->getJson(route('bi.ventas-global.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-05', 'sistema' => 'delta']))
            ->assertOk()
            ->assertJsonCount(1, 'plataformas')
            ->assertJsonPath('plataformas.0.key', 'delta')
            ->assertJsonPath('terminales.total', 1)
            ->assertJsonPath('venta_global.total', 1100)
            ->assertJsonPath('premios.total', 200)
            ->assertJsonPath('grafico.6.total', 1100)
            ->assertJsonMissingPath('grafico.6.real');
    }

    public function test_global_dashboard_rejects_an_unknown_system(): void
    {
        $this->withoutMiddleware()
            ->getJson(route('bi.ventas-global.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-05', 'sistema' => 'otro']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('sistema');
    }

    public function test_global_dashboard_requires_a_valid_date_range(): void
    {
        $this->withoutMiddleware()
            ->getJson(route('bi.ventas-global.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-01']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('fecha_hasta');
    }

    public function test_global_view_lists_the_three_platforms_and_a_stacked_chart(): void
    {
        $view = file_get_contents(resource_path('views/bi/ventas-global.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/BiVentasGlobalController.php'));

        $this->assertStringContainsString("route('bi.ventas-global.data')", $view);
        $this->assertStringContainsString('stacked: true', $view);
        $this->assertStringContainsString('Ventas últimos 7 días por plataforma', $view);
        $this->assertStringContainsString("'real' => ['nombre' => 'Lotobet Real'", $controller);
        $this->assertStringContainsString("'lotodom' => ['nombre' => 'Lotodom'", $controller);
        $this->assertStringContainsString("'delta' => ['nombre' => 'Delta'", $controller);
    }
}
