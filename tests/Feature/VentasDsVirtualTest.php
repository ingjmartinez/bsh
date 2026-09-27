<?php

namespace Tests\Feature;

use App\Models\Token;
use App\Models\VentaDsVirtual;
use App\Services\AutoProcesoService;
use App\Services\Lotobet\LotobetSessionService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\TestCase;

class VentasDsVirtualTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $migration = require database_path('migrations/2026_09_10_101051_create_venta_ds_virtuals_table.php');
        $migration->up();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('ventas_ds_virtual');

        parent::tearDown();
    }

    public function test_submodule_routes_menu_and_table_are_registered(): void
    {
        $this->assertTrue(Route::has('ventas-ds-virtual.index'));
        $this->assertTrue(Route::has('ventas-ds-virtual.generate'));
        $this->assertTrue(Route::has('ventas-ds-virtual.data'));
        $this->assertTrue(Route::has('ventas-ds-virtual.sync'));
        $this->assertTrue(Route::has('ventas-ds-virtual.destroy'));
        $this->assertTrue(Schema::hasColumns('ventas_ds_virtual', [
            'consorcio_id',
            'fecha',
            'agencia_id',
            'ventas',
            'premios_pagados',
            'proveedor_id',
            'premios',
            'proveedor_nombre',
        ]));

        $layout = file_get_contents(resource_path('views/app.blade.php'));
        $listView = file_get_contents(resource_path('views/lotobet/ventas-ds-virtual-listado.blade.php'));
        $generateView = file_get_contents(resource_path('views/lotobet/ventas-ds-virtual.blade.php'));
        $this->assertIsString($layout);
        $this->assertIsString($listView);
        $this->assertIsString($generateView);
        $this->assertStringContainsString("route('ventas-ds-virtual.index')", $layout);
        $this->assertStringContainsString("route('ventas-ds-virtual.generate')", $layout);
        $this->assertStringContainsString('Ventas DS Virtual', $layout);
        $this->assertSame(1, substr_count($layout, "route('ventas-ds-virtual.index')"));
        $this->assertSame(1, substr_count($layout, "route('ventas-ds-virtual.generate')"));
        $this->assertLessThan(
            strpos($layout, "route('ventas-ds-virtual.index')"),
            strpos($layout, "route('ventas-ds-virtual.generate')")
        );
        $this->assertLessThan(
            strpos($layout, 'href="#sidebarEmail"'),
            strpos($layout, "route('ventas-ds-virtual.index')")
        );
        foreach ([
            'btnGenerarTokenDsVirtual',
            'btnGenerarData',
            'btnGuardarData',
            'btnEliminarData',
            'btnGenerarDataFecha',
            'btnGuardarDataFecha',
            'btnEliminarDataFecha',
            'tableVentasDsVirtual',
            'modalRangoDsVirtual',
        ] as $elementId) {
            $this->assertStringContainsString('id="'.$elementId.'"', $listView);
            $this->assertStringNotContainsString('id="'.$elementId.'"', $generateView);
        }
        $this->assertStringNotContainsString("route('ventas-ds-virtual.generate')", $listView);
        $this->assertStringContainsString('Configurar Token', $listView);
        $this->assertStringContainsString('id="btnGenerarTokenDsVirtual"', $listView);
        $this->assertStringContainsString("url('/generar-token')", $listView);
        $this->assertStringContainsString("route('ventas-ds-virtual.data')", $listView);
        $this->assertStringContainsString("route('ventas-ds-virtual.sync')", $listView);
        $this->assertStringContainsString("route('ventas-ds-virtual.destroy')", $listView);
        $this->assertStringContainsString('formatMoney(item.ventas)', $listView);
        $this->assertStringContainsString('formatMoney(item.premios_pagados)', $listView);
        $this->assertStringContainsString("buttons: ['copy', 'csv', 'excel', 'pdf', 'print']", $listView);

        foreach ([
            'btnGenerarToken',
            'btnProcesarUno',
            'btnEliminarFecha',
            'btnProcesarRango',
            'btnEliminarRango',
            'btnConfigAuto',
            'statusTable',
            'logContainer',
            'modalRango',
            'modalConfigAuto',
        ] as $elementId) {
            $this->assertStringContainsString('id="'.$elementId.'"', $generateView);
            $this->assertStringNotContainsString('id="'.$elementId.'"', $listView);
        }
        $this->assertStringContainsString("url('/auto-proceso/ds_virtual/config')", $generateView);
        $this->assertStringContainsString('Generar Token DS Virtual', $generateView);
        $this->assertStringContainsString("url('/generar-token')", $generateView);
        $this->assertStringContainsString("route('ventas-ds-virtual.index')", $generateView);
    }

    public function test_listado_y_proceso_masivo_usan_vistas_separadas(): void
    {
        $this->withoutMiddleware()
            ->get(route('ventas-ds-virtual.index'))
            ->assertOk()
            ->assertViewIs('lotobet.ventas-ds-virtual-listado');

        $this->withoutMiddleware()
            ->get(route('ventas-ds-virtual.generate'))
            ->assertOk()
            ->assertViewIs('lotobet.ventas-ds-virtual');
    }

    public function test_sync_uses_lotobet_session_and_is_idempotent(): void
    {
        $firstResponse = $this->apiResponse(17200, 9430);
        $updatedResponse = $this->apiResponse(18000, 9500);

        $this->mock(LotobetSessionService::class, function (MockInterface $mock) use ($firstResponse, $updatedResponse): void {
            $mock->shouldReceive('getVentasDsVirtual')
                ->twice()
                ->with('2026-09-09')
                ->andReturn($firstResponse, $updatedResponse);
        });

        $this->withoutMiddleware()
            ->postJson(route('ventas-ds-virtual.sync'), ['fecha' => '2026-09-09'])
            ->assertOk()
            ->assertJsonPath('received', 1)
            ->assertJsonPath('stored', 1);

        $this->withoutMiddleware()
            ->postJson(route('ventas-ds-virtual.sync'), ['fecha' => '2026-09-09'])
            ->assertOk()
            ->assertJsonPath('stored', 1);

        $this->assertDatabaseCount('ventas_ds_virtual', 1);
        $this->assertDatabaseHas('ventas_ds_virtual', [
            'fecha' => '2026-09-09',
            'agencia_id' => '0710162',
            'ventas' => 18000,
            'premios_pagados' => 9500,
            'proveedor_nombre' => 'DS Virtual',
        ]);
    }

    public function test_data_endpoint_returns_normalized_api_content(): void
    {
        $this->mock(LotobetSessionService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getVentasDsVirtual')
                ->once()
                ->with('2026-09-09')
                ->andReturn($this->apiResponse(17200, 9430));
        });

        $this->withoutMiddleware()
            ->getJson(route('ventas-ds-virtual.data', ['fecha' => '2026-09-09']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('ventas.0.agencia_id', '0710162')
            ->assertJsonPath('ventas.0.proveedor_id', 2)
            ->assertJsonPath('ventas.0.ventas', 17200);
    }

    public function test_ds_virtual_can_run_as_an_automatic_process(): void
    {
        $this->mock(LotobetSessionService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generateToken')
                ->once()
                ->andReturn(new Token([
                    'id' => 1,
                    'token' => 'test-token',
                    'fecha' => now()->addHour(),
                ]));
            $mock->shouldReceive('getVentasDsVirtual')
                ->once()
                ->with('2026-09-09')
                ->andReturn($this->apiResponse(17200, 9430));
        });

        $result = app(AutoProcesoService::class)->execute('ds_virtual', '2026-09-09');

        $this->assertTrue($result['ok']);
        $this->assertSame(2, $result['ok_count']);
        $this->assertSame('Ventas DS Virtual', $result['details'][1]['modulo']);
        $this->assertSame(1, $result['details'][1]['total']);
        $this->assertDatabaseCount('ventas_ds_virtual', 1);
    }

    public function test_invalid_date_is_rejected_before_calling_external_api(): void
    {
        $this->withoutMiddleware()
            ->getJson(route('ventas-ds-virtual.data', ['fecha' => '09-09-2026']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fecha');
    }

    public function test_saved_records_can_be_deleted_by_date(): void
    {
        VentaDsVirtual::factory()->create(['fecha' => '2026-09-09']);
        VentaDsVirtual::factory()->create(['fecha' => '2026-09-10']);

        $this->withoutMiddleware()
            ->deleteJson(route('ventas-ds-virtual.destroy'), ['fecha' => '2026-09-09'])
            ->assertOk()
            ->assertJsonPath('deleted', 1);

        $this->assertDatabaseMissing('ventas_ds_virtual', ['fecha' => '2026-09-09']);
        $this->assertSame(
            1,
            VentaDsVirtual::query()->whereDate('fecha', '2026-09-10')->count()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function apiResponse(float $ventas, float $premiosPagados): array
    {
        return [
            'Content' => [[
                'consorcio_id' => 7,
                'fecha' => '2026-09-09',
                'agencia_id' => '0710162',
                'ventas' => $ventas,
                'premios_pagados' => $premiosPagados,
                'proveedor_id' => 2,
                'premios' => 9430,
                'proveedor_nombre' => 'DS Virtual',
            ]],
        ];
    }
}
