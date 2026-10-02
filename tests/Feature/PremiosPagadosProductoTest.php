<?php

namespace Tests\Feature;

use App\Models\Agencia;
use App\Models\CatalogoJuego;
use App\Models\PagoAOtraEmpresa;
use App\Models\PagoMismaEmpresa;
use App\Services\PremiosPagadosProductoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PremiosPagadosProductoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('terminal')->nullable();
        });
        Schema::create('catalogo_juegos', function (Blueprint $table): void {
            $table->id();
            $table->integer('producto_id')->unique();
            $table->string('descripcion');
            $table->string('tipo');
        });
        foreach (['pagos_misma_empresa_bet', 'pagos_aotra_empresa_bet'] as $nombre) {
            Schema::create($nombre, function (Blueprint $table): void {
                $table->id();
                $table->string('agencia_id');
                $table->date('fecha');
                $table->decimal('monto', 14, 2);
            });
        }
        Agencia::query()->insert([['terminal' => '00101'], ['terminal' => '101'], ['terminal' => null], ['terminal' => '']]);
        CatalogoJuego::query()->insert([
            ['producto_id' => 7, 'descripcion' => 'QUINIELA', 'tipo' => 'Tradicional'],
            ['producto_id' => 400, 'descripcion' => 'LOTO REAL', 'tipo' => 'No Tradicional'],
            ['producto_id' => 401, 'descripcion' => 'PEGA 3', 'tipo' => 'no_tradicional'],
        ]);
    }

    public function test_legacy_payments_filter_terminals_and_dates_without_duplicates_or_invented_products(): void
    {
        PagoMismaEmpresa::query()->insert([
            ['agencia_id' => '101', 'fecha' => '2026-09-01', 'monto' => 100.25],
            ['agencia_id' => '00101', 'fecha' => '2026-09-30', 'monto' => 200.50],
            ['agencia_id' => '999', 'fecha' => '2026-09-10', 'monto' => 999],
            ['agencia_id' => '101', 'fecha' => '2026-10-01', 'monto' => 888],
            ['agencia_id' => '', 'fecha' => '2026-09-10', 'monto' => 777],
        ]);
        PagoAOtraEmpresa::query()->insert(['agencia_id' => '101', 'fecha' => '2026-09-15', 'monto' => 50]);
        $reporte = $this->report();
        $this->assertSame(['misma_empresa' => 300.75, 'otra_empresa' => 50.0, 'total' => 350.75], $reporte['resumen']);
        $this->assertNull($reporte['grupos'][0]['total']);
        $this->assertNull($reporte['grupos'][1]['productos'][0]['total']);
        $this->assertSame(350.75, $reporte['grupos'][2]['total']);
        $this->assertCount(2, $reporte['grupos'][1]['productos']);
        $this->assertSame([
            ['terminal' => '101', 'misma_empresa' => 300.75, 'otra_empresa' => 50.0, 'total' => 350.75],
        ], $reporte['grupos'][2]['productos'][0]['terminales']);
    }

    public function test_identified_payments_group_by_catalogue_and_keep_unknown_products_separate(): void
    {
        $this->addProductColumn('pagos_misma_empresa_bet');
        $this->addProductColumn('pagos_aotra_empresa_bet');
        PagoMismaEmpresa::query()->insert([
            ['agencia_id' => '101', 'fecha' => '2026-09-01', 'monto' => 100, 'producto_id' => 7],
            ['agencia_id' => '101', 'fecha' => '2026-09-02', 'monto' => 200, 'producto_id' => 400],
            ['agencia_id' => '101', 'fecha' => '2026-09-02', 'monto' => 25, 'producto_id' => null],
            ['agencia_id' => '101', 'fecha' => '2026-09-02', 'monto' => 15, 'producto_id' => 999],
        ]);
        PagoAOtraEmpresa::query()->insert(['agencia_id' => '101', 'fecha' => '2026-09-01', 'monto' => 50, 'producto_id' => 400]);
        $reporte = $this->report();
        $this->assertSame(100.0, $reporte['grupos'][0]['total']);
        $this->assertSame(250.0, $reporte['grupos'][1]['total']);
        $this->assertSame(200.0, $reporte['grupos'][1]['productos'][0]['misma_empresa']);
        $this->assertSame(50.0, $reporte['grupos'][1]['productos'][0]['otra_empresa']);
        $this->assertSame(40.0, $reporte['grupos'][2]['total']);
        $this->assertSame(390.0, $reporte['resumen']['total']);
        $this->assertSame([], $reporte['fuentes_sin_producto']);
    }

    public function test_partial_availability_keeps_all_payments_in_summary(): void
    {
        $this->addProductColumn('pagos_misma_empresa_bet');
        PagoMismaEmpresa::query()->insert(['agencia_id' => '101', 'fecha' => '2026-09-01', 'monto' => 100, 'producto_id' => 400]);
        PagoAOtraEmpresa::query()->insert(['agencia_id' => '101', 'fecha' => '2026-09-01', 'monto' => 50]);
        $reporte = $this->report();
        $this->assertSame(150.0, $reporte['resumen']['total']);
        $this->assertSame(100.0, $reporte['grupos'][1]['productos'][0]['misma_empresa']);
        $this->assertNull($reporte['grupos'][1]['productos'][0]['otra_empresa']);
        $this->assertNull($reporte['grupos'][1]['total']);
        $this->assertNull($reporte['grupos'][1]['productos'][0]['terminales'][0]['otra_empresa']);
        $this->assertNull($reporte['grupos'][1]['productos'][0]['terminales'][0]['total']);
    }

    public function test_cascade_keeps_product_and_terminal_totals_separate_and_merges_terminal_formats(): void
    {
        $this->addProductColumn('pagos_misma_empresa_bet');
        $this->addProductColumn('pagos_aotra_empresa_bet');
        Agencia::query()->insert(['terminal' => '00102']);
        PagoMismaEmpresa::query()->insert([
            ['agencia_id' => '101', 'fecha' => '2026-09-01', 'monto' => 100, 'producto_id' => 400],
            ['agencia_id' => '00101', 'fecha' => '2026-09-02', 'monto' => 25, 'producto_id' => 400],
            ['agencia_id' => '102', 'fecha' => '2026-09-02', 'monto' => 200, 'producto_id' => 400],
            ['agencia_id' => '101', 'fecha' => '2026-09-02', 'monto' => 75, 'producto_id' => 401],
        ]);
        PagoAOtraEmpresa::query()->insert(['agencia_id' => '00101', 'fecha' => '2026-09-01', 'monto' => 50, 'producto_id' => 400]);

        $reporte = $this->report();
        $productos = $reporte['grupos'][1]['productos'];
        $this->assertSame(450.0, $reporte['grupos'][1]['total']);
        $this->assertSame(375.0, $productos[0]['total']);
        $this->assertSame([
            ['terminal' => '101', 'misma_empresa' => 125.0, 'otra_empresa' => 50.0, 'total' => 175.0],
            ['terminal' => '102', 'misma_empresa' => 200.0, 'otra_empresa' => 0, 'total' => 200.0],
        ], $productos[0]['terminales']);
        $this->assertSame(75.0, $productos[1]['terminales'][0]['total']);

        View::share('errors', new ViewErrorBag);
        $response = $this->withoutMiddleware()->get(route('reportes.premios-pagados-productos', [
            'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-09-30',
        ]))->assertOk()->assertSee('Terminal')->assertSee('RD$ 175.00');
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(3, $xpath->query('//details//details[contains(@class, "producto-detalle")]')->length);
        $this->assertSame(2, $xpath->query('//details[contains(@class, "producto-detalle")][summary/strong="LOTO REAL"]//tbody/tr')->length);
    }

    public function test_empty_period_has_zero_summary(): void
    {
        $reporte = $this->report();
        $this->assertSame(0.0, $reporte['resumen']['total']);
        $this->assertSame([], $reporte['grupos'][2]['productos']);
        $this->assertNull($reporte['grupos'][1]['total']);
        $this->assertNull($reporte['disponibilidad'][0]['fecha_inicio']);
        $this->assertSame(0, $reporte['disponibilidad'][0]['registros_periodo']);
    }

    public function test_period_without_imported_payments_shows_available_dates(): void
    {
        PagoMismaEmpresa::query()->insert([
            ['agencia_id' => '101', 'fecha' => '2026-05-01', 'monto' => 100],
            ['agencia_id' => '101', 'fecha' => '2026-06-11', 'monto' => 200],
        ]);
        $reporte = $this->report();
        $this->assertSame('2026-05-01', $reporte['disponibilidad'][0]['fecha_inicio']);
        $this->assertSame('2026-06-11', $reporte['disponibilidad'][0]['fecha_fin']);
        $this->assertSame(0, $reporte['disponibilidad'][0]['registros_periodo']);
        View::share('errors', new ViewErrorBag);
        $this->withoutMiddleware()->get(route('reportes.premios-pagados-productos', [
            'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-09-30',
        ]))->assertOk()->assertSee('No se encontraron pagos')
            ->assertSee('2026-05-01 al 2026-06-11')
            ->assertSee('No hay pagos guardados para las fechas consultadas.')
            ->assertSee('No hay pagos guardados en esta fuente.')
            ->assertDontSee('Hay pagos cuya información no permite identificar el producto.');
    }

    public function test_unmatched_terminals_are_distinguished_from_missing_period_data(): void
    {
        PagoMismaEmpresa::query()->insert(['agencia_id' => '999', 'fecha' => '2026-09-01', 'monto' => 100]);
        $this->assertSame(1, $this->report()['disponibilidad'][0]['registros_periodo']);
        View::share('errors', new ViewErrorBag);
        $this->withoutMiddleware()->get(route('reportes.premios-pagados-productos', [
            'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-09-30',
        ]))->assertOk()->assertSee('sus terminales no coinciden con las registradas en agencias Real.');
    }

    public function test_zero_amount_payments_are_not_reported_as_missing_data(): void
    {
        PagoMismaEmpresa::query()->insert(['agencia_id' => '101', 'fecha' => '2026-09-01', 'monto' => 0]);
        $reporte = $this->report();
        $this->assertSame(0.0, $reporte['resumen']['total']);
        $this->assertSame([], $reporte['disponibilidad']);
    }

    public function test_page_renders_categories_filters_and_catalogue(): void
    {
        View::share('errors', new ViewErrorBag);
        $this->withoutMiddleware()->get(route('reportes.premios-pagados-productos', [
            'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-09-30',
        ]))->assertOk()->assertSee('Premios pagados por productos')->assertSee('No tradicionales')
            ->assertSee('LOTO REAL')->assertSee('<details', false)->assertSee('Sin producto identificado')
            ->assertSee('id="formPremiosPagados"', false)
            ->assertSee("premiosForm.addEventListener('submit'", false)
            ->assertSee("title: 'Consultando...'", false)
            ->assertSee('didOpen: () => Swal.showLoading()', false)
            ->assertSee('allowOutsideClick: false', false)
            ->assertSee('allowEscapeKey: false', false)
            ->assertSee('consultarButton.disabled = true', false)
            ->assertSee("title: 'Revise las fechas'", false);
        $this->assertTrue(collect(config('reportes'))->contains('url', '/reportes/premios-pagados-productos'));
    }

    public function test_invalid_dates_are_rejected(): void
    {
        $this->withoutMiddleware()->getJson(route('reportes.premios-pagados-productos', [
            'fecha_inicio' => '2026-09-30', 'fecha_fin' => '2026-09-01',
        ]))->assertUnprocessable()->assertJsonValidationErrors('fecha_fin');
    }

    public function test_missing_end_date_is_rejected(): void
    {
        $this->withoutMiddleware()->getJson(route('reportes.premios-pagados-productos', [
            'fecha_inicio' => '2026-09-01',
        ]))->assertUnprocessable()->assertJsonValidationErrors('fecha_fin');
    }

    public function test_report_requires_authentication(): void
    {
        $this->get(route('reportes.premios-pagados-productos'))->assertRedirect();
    }

    private function addProductColumn(string $table): void
    {
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->integer('producto_id')->nullable();
        });
    }

    private function report(): array
    {
        return app(PremiosPagadosProductoService::class)->report('2026-09-01', '2026-09-30');
    }
}
