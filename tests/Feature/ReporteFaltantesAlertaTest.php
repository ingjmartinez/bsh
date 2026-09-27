<?php

namespace Tests\Feature;

use App\Http\Requests\GuardarConfiguracionAlertaFaltantesRequest;
use App\Models\ReporteFaltantesAlertaConfiguracion;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ReporteFaltantesAlertaTest extends TestCase
{
    public function test_alert_configuration_validates_both_thresholds(): void
    {
        $request = new GuardarConfiguracionAlertaFaltantesRequest;

        $this->assertTrue(Validator::make([
            'minimo_faltantes' => 3,
            'maximo_monto' => 5000,
        ], $request->rules())->passes());

        $this->assertFalse(Validator::make([
            'minimo_faltantes' => 0,
            'maximo_monto' => -1,
        ], $request->rules())->passes());
    }

    public function test_alert_configuration_model_uses_expected_defaults_and_casts(): void
    {
        $model = new ReporteFaltantesAlertaConfiguracion([
            'minimo_faltantes' => '3',
            'maximo_monto' => '5000',
        ]);

        $this->assertSame('reporte_faltantes_alerta_configuraciones', $model->getTable());
        $this->assertSame(3, $model->minimo_faltantes);
        $this->assertSame('5000.00', $model->maximo_monto);
    }

    public function test_report_exposes_configuration_routes_and_alert_interface(): void
    {
        $this->assertNotNull(Route::getRoutes()->match(
            \Illuminate\Http\Request::create('/recursos-humanos/faltantes/configuracion-alerta', 'GET')
        ));
        $this->assertNotNull(Route::getRoutes()->match(
            \Illuminate\Http\Request::create('/recursos-humanos/faltantes/configuracion-alerta', 'PUT')
        ));

        $view = file_get_contents(resource_path('views/reportes/faltantes-bet.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString('cardAlertasFaltantes', $view);
        $this->assertStringContainsString('configurarAlertaFaltantesModal', $view);
        $this->assertStringContainsString('alertasFaltantesModal', $view);
        $this->assertStringContainsString("params.append('solo_alertas', '1')", $view);
        $this->assertStringContainsString('btnAlternarColumnasAlerta', $view);
        $this->assertStringContainsString('cantidadFaltantes > 2', $view);
        $this->assertStringContainsString("route('recursos-humanos.faltantes.index')", $view);
        $this->assertStringContainsString("route('recursos-humanos.index')", $view);
    }

    public function test_report_is_listed_in_human_resources_and_hidden_from_reports(): void
    {
        $humanResourcesReport = collect(config('recursos_humanos'))
            ->firstWhere('url', '/recursos-humanos/faltantes');
        $legacyReport = collect(config('reportes'))
            ->firstWhere('url', '/reportes-faltantes-lotobet');

        $this->assertNotNull($humanResourcesReport);
        $this->assertTrue($humanResourcesReport['activo']);
        $this->assertNotNull($legacyReport);
        $this->assertFalse($legacyReport['activo']);
        $this->assertTrue(Route::has('recursos-humanos.faltantes.index'));
        $this->assertTrue(Route::has('recursos-humanos.faltantes.configuracion-alerta.update'));
    }

    public function test_alert_query_accepts_minimum_count_or_amount_threshold(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/ReporteController.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString('(COUNT(faltantes.fila_id) >= ? OR SUM(faltantes.monto) >= ?)', $controller);
    }

    public function test_report_hides_redundant_card_title_and_explains_or_condition(): void
    {
        $view = file_get_contents(resource_path('views/reportes/faltantes-bet.blade.php'));

        $this->assertIsString($view);
        $this->assertStringNotContainsString('Reporte de Faltantes por Cedula - Todos los sistemas', $view);
        $this->assertStringContainsString('cuando se cumple cualquiera de las dos condiciones', $view);
        $this->assertStringContainsString('faltantes o monto desde', $view);
    }
}
