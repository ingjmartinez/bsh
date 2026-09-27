<?php

namespace Tests\Feature;

use App\Http\Requests\BiLotobetDashboardRequest;
use App\Http\Requests\BiLotobetMonthlyRequest;
use App\Http\Requests\BiLotobetProductosRequest;
use App\Http\Requests\BiLotobetRazaRequest;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BiLotobetDashboardTest extends TestCase
{
    public function test_dashboard_request_validates_dates_and_filters(): void
    {
        $rules = (new BiLotobetDashboardRequest)->rules();

        $this->assertTrue((new BiLotobetDashboardRequest)->authorize());
        $this->assertContains('required', $rules['fecha_desde']);
        $this->assertContains('date_format:Y-m-d', $rules['fecha_hasta']);
        $this->assertContains('after_or_equal:fecha_desde', $rules['fecha_hasta']);
        $this->assertArrayHasKey('grupo', $rules);
        $this->assertArrayHasKey('central', $rules);
        $this->assertArrayHasKey('gerente', $rules);
        $this->assertArrayHasKey('tipo_pago', $rules);
    }

    public function test_lotobet_dashboard_has_data_endpoint_and_reference_design_elements(): void
    {
        $route = Route::getRoutes()->getByName('bi.lotobet-real.data');
        $view = file_get_contents(resource_path('views/bi/lotobet-real.blade.php'));

        $this->assertNotNull($route);
        $this->assertStringContainsString('BiLotobetController@data', $route->getActionName());
        $this->assertStringContainsString('Venta global', $view);
        $this->assertStringContainsString('Ventas últimos 7 días', $view);
        $this->assertStringContainsString('Filtro detalle por terminal', $view);
        $this->assertStringContainsString("colors: ['#dfc963']", $view);
        $this->assertStringContainsString("asset('libs/apexcharts/apexcharts.min.js')", $view);
        $this->assertStringContainsString("typeof ApexCharts === 'undefined'", $view);
    }

    public function test_lotobet_platform_page_exposes_dashboard_as_a_report_card(): void
    {
        $view = file_get_contents(resource_path('views/bi/lotobet-real-index.blade.php'));

        $this->assertStringContainsString("route('bi.lotobet-real.dashboard')", $view);
        $this->assertStringContainsString('Tablero de ventas', $view);
        $this->assertStringContainsString('Reportes disponibles', $view);
        $this->assertStringContainsString("route('bi.lotobet-real.monthly')", $view);
        $this->assertStringContainsString('Tendencia mensual', $view);
    }

    public function test_monthly_report_has_routes_filters_and_two_charts(): void
    {
        $rules = (new BiLotobetMonthlyRequest)->rules();
        $view = file_get_contents(resource_path('views/bi/lotobet-real-mensual.blade.php'));

        $this->assertTrue((new BiLotobetMonthlyRequest)->authorize());
        $this->assertArrayHasKey('terminal', $rules);
        $this->assertNotNull(Route::getRoutes()->getByName('bi.lotobet-real.monthly'));
        $this->assertNotNull(Route::getRoutes()->getByName('bi.lotobet-real.monthly-data'));
        $this->assertStringContainsString('monthlyGlobalChart', $view);
        $this->assertStringContainsString('monthlyAverageChart', $view);
        $this->assertStringContainsString('Venta Global', $view);
        $this->assertStringContainsString('Promedio', $view);
        $this->assertStringContainsString("dataLabels: { position: 'top' }", $view);
        $this->assertStringContainsString("fontFamily: 'inherit', fontWeight: 600", $view);
        $this->assertStringContainsString('data.meses.filter(item => Number(item.total) > 0)', $view);
        $this->assertStringContainsString('text: money(item.total)', $view);
        $this->assertStringContainsString("style: { background: '#e4cf6d', color: '#111'", $view);
        $this->assertStringContainsString("background: { enabled: true, foreColor: '#111'", $view);
    }

    public function test_raza_sales_are_sourced_from_ds_virtual(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/BiLotobetController.php'));

        $this->assertStringContainsString("DB::table('ventas_ds_virtual as d')", $controller);
        $this->assertStringContainsString("\$porCategoria['raza'] = \$ventaRaza", $controller);
        $this->assertStringContainsString("sum('d.ventas')", $controller);
    }

    public function test_ventas_raza_card_and_report_use_ds_virtual_metrics(): void
    {
        $index = file_get_contents(resource_path('views/bi/lotobet-real-index.blade.php'));
        $view = file_get_contents(resource_path('views/bi/lotobet-real-raza.blade.php'));
        $rules = (new BiLotobetRazaRequest)->rules();

        $this->assertStringContainsString("route('bi.lotobet-real.raza')", $index);
        $this->assertStringContainsString('Ventas Raza', $index);
        $this->assertStringContainsString('Premios Raza', $view);
        $this->assertStringContainsString('Resultado bruto', $view);
        $this->assertStringContainsString('Últimos 7 días', $view);
        $this->assertStringContainsString('raza-content-grid', $view);
        $this->assertStringContainsString('grid-template-columns:190px 255px 145px minmax(420px,1fr)', $view);
        $this->assertArrayHasKey('fecha_desde', $rules);
        $this->assertNotNull(Route::getRoutes()->getByName('bi.lotobet-real.raza-data'));
    }

    public function test_productos_report_has_category_filter_and_charts(): void
    {
        $index = file_get_contents(resource_path('views/bi/lotobet-real-index.blade.php'));
        $view = file_get_contents(resource_path('views/bi/lotobet-real-productos.blade.php'));
        $rules = (new BiLotobetProductosRequest)->rules();

        $this->assertStringContainsString("route('bi.lotobet-real.productos')", $index);
        $this->assertStringContainsString('Por Productos', $index);
        $this->assertStringContainsString('Tradicional', $view);
        $this->assertStringContainsString('No tradicional', $view);
        $this->assertStringContainsString('prodDailyChart', $view);
        $this->assertStringContainsString('prodPieChart', $view);
        $this->assertContains('in:tradicional,no_tradicional', $rules['categoria']);
        $this->assertNotNull(Route::getRoutes()->getByName('bi.lotobet-real.productos'));
        $this->assertNotNull(Route::getRoutes()->getByName('bi.lotobet-real.productos-data'));
    }
}
