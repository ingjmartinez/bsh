<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BiPlataformaDashboardTest extends TestCase
{
    private const TABLAS = [
        'agencias_lotedom', 'agencias_delta', 'ventas_usuarios_net', 'ventas_flash_bet', 'premios_net', 'catalogo_juegos',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->dropTablas();

        foreach (['agencias_lotedom', 'agencias_delta'] as $tabla) {
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

        Schema::create('ventas_usuarios_net', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia_id')->nullable();
            $table->bigInteger('producto_id')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('tipo')->nullable();
            $table->decimal('monto', 14, 2)->default(0);
            $table->date('fecha');
        });
        Schema::create('premios_net', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia_id')->nullable();
            $table->decimal('monto', 14, 2)->default(0);
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

    public function test_lotodom_dashboard_reads_ventas_usuarios_net_and_premios_net(): void
    {
        DB::table('agencias_lotedom')->insert([
            ['terminal' => '100', 'estatus' => 1, 'grupo' => 'G1'],
            ['terminal' => '200', 'estatus' => 0, 'grupo' => 'G2'],
        ]);
        DB::table('ventas_usuarios_net')->insert([
            ['agencia_id' => '100', 'producto_id' => 1, 'tipo' => 'boleto', 'monto' => 1000, 'fecha' => '2026-10-05'],
            ['agencia_id' => '100', 'producto_id' => -1, 'tipo' => 'recarga', 'monto' => 500, 'fecha' => '2026-10-05'],
            ['agencia_id' => '100', 'producto_id' => 1, 'tipo' => 'boleto', 'monto' => 500, 'fecha' => '2026-09-28'],
        ]);
        DB::table('premios_net')->insert([['agencia_id' => '100', 'monto' => 300, 'fecha' => '2026-10-05']]);

        $this->withoutMiddleware()
            ->getJson(route('bi.lotodom.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-05']))
            ->assertOk()
            ->assertJsonPath('terminales.total', 2)
            ->assertJsonPath('terminales.activas', 1)
            ->assertJsonPath('terminales.inactivas', 1)
            ->assertJsonPath('terminales.vendieron', 1)
            ->assertJsonPath('terminales.sin_ventas', 1)
            ->assertJsonPath('venta_global.total', 1500)
            ->assertJsonPath('venta_global.variacion', 200)
            ->assertJsonPath('premios.total', 300)
            ->assertJsonPath('categorias.tradicional.total', 1000)
            ->assertJsonPath('categorias.recargas.total', 500)
            ->assertJsonPath('resultado_bruto.total', 1200)
            ->assertJsonCount(7, 'grafico')
            ->assertJsonPath('grafico.6.total', 1500);
    }

    public function test_lotodom_dashboard_applies_agency_filters(): void
    {
        DB::table('agencias_lotedom')->insert([
            ['terminal' => '100', 'estatus' => 1, 'grupo' => 'G1'],
            ['terminal' => '200', 'estatus' => 1, 'grupo' => 'G2'],
        ]);
        DB::table('ventas_usuarios_net')->insert([
            ['agencia_id' => '100', 'producto_id' => 1, 'tipo' => 'boleto', 'monto' => 1000, 'fecha' => '2026-10-05'],
            ['agencia_id' => '200', 'producto_id' => 1, 'tipo' => 'boleto', 'monto' => 400, 'fecha' => '2026-10-05'],
        ]);

        $this->withoutMiddleware()
            ->getJson(route('bi.lotodom.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-05', 'grupo' => 'G2']))
            ->assertOk()
            ->assertJsonPath('terminales.total', 1)
            ->assertJsonPath('venta_global.total', 400);
    }

    public function test_delta_dashboard_reads_ventas_flash_bet(): void
    {
        DB::table('agencias_delta')->insert([
            ['terminal' => '555', 'estatus' => 1],
            ['terminal' => '777', 'estatus' => 1],
        ]);
        DB::table('ventas_flash_bet')->insert([
            ['numero_externo' => 555, 'venta_loteria' => 800, 'premios_pagado' => 150, 'venta_recarga' => 200, 'ventas_no_tradicional' => 100, 'premios_pagados_no_tradicional' => 50, 'fecha' => '2026-10-05'],
            ['numero_externo' => 555, 'venta_loteria' => 550, 'premios_pagado' => 0, 'venta_recarga' => 0, 'ventas_no_tradicional' => 0, 'premios_pagados_no_tradicional' => 0, 'fecha' => '2026-09-28'],
        ]);

        $this->withoutMiddleware()
            ->getJson(route('bi.delta.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-05']))
            ->assertOk()
            ->assertJsonPath('terminales.total', 2)
            ->assertJsonPath('terminales.vendieron', 1)
            ->assertJsonPath('terminales.sin_ventas', 1)
            ->assertJsonPath('venta_global.total', 1100)
            ->assertJsonPath('venta_global.variacion', 100)
            ->assertJsonPath('premios.total', 200)
            ->assertJsonPath('categorias.tradicional.total', 800)
            ->assertJsonPath('categorias.recargas.total', 200)
            ->assertJsonPath('categorias.no_tradicional.total', 100)
            ->assertJsonMissingPath('categorias.raza')
            ->assertJsonPath('resultado_bruto.total', 900)
            ->assertJsonPath('grafico.6.total', 1100);
    }

    public function test_data_endpoint_requires_a_valid_date_range(): void
    {
        $this->withoutMiddleware()
            ->getJson(route('bi.delta.data', ['fecha_desde' => '2026-10-05', 'fecha_hasta' => '2026-10-01']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('fecha_hasta');
    }

    public function test_platform_view_is_a_parameterized_clone_of_the_lotobet_dashboard(): void
    {
        $view = file_get_contents(resource_path('views/bi/plataforma.blade.php'));

        $this->assertStringContainsString('Venta global', $view);
        $this->assertStringContainsString('Ventas últimos 7 días', $view);
        $this->assertStringContainsString("colors: ['#dfc963']", $view);
        $this->assertStringContainsString("route('bi.'.\$plataforma.'.data')", $view);
        $this->assertStringContainsString('{{ $nombre }}', $view);
    }
}
