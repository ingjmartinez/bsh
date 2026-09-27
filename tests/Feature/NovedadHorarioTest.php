<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class NovedadHorarioTest extends TestCase
{
    public function test_controller_uses_the_columns_available_in_each_agency_catalog(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/NovedadHorarioController.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString('MAX(nombre) AS nombre_agencia', $controller);
        $this->assertStringContainsString('FROM agencias_lotedom', $controller);
        $this->assertStringContainsString("MAX(COALESCE(NULLIF(nombre_agencia, ''), nombre))", $controller);
        $this->assertSame(2, substr_count($controller, 'CHAR_LENGTH(REPLACE(REPLACE(TRIM(COALESCE('));
    }

    public function test_view_handles_ajax_errors_without_datatables_native_alert(): void
    {
        $view = file_get_contents(resource_path('views/recursos_humanos/novedades_de_horario/index.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString("$.fn.dataTable.ext.errMode = 'none'", $view);
        $this->assertStringContainsString('No se pudieron consultar las novedades de horario.', $view);
        $this->assertStringContainsString("title: 'Consultando novedades'", $view);
        $this->assertStringContainsString('didOpen: () => Swal.showLoading()', $view);
        $this->assertStringContainsString('allowOutsideClick: false', $view);
    }

    public function test_report_includes_calculation_detail_and_export_features(): void
    {
        $view = file_get_contents(resource_path('views/recursos_humanos/novedades_de_horario/index.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString('btnConfigurarHorario', $view);
        $this->assertStringContainsString('detalle_filtro', $view);
        $this->assertStringContainsString('detalleFaltantesHorarioModal', $view);
        $this->assertStringContainsString('btnExportarExcel', $view);
        $this->assertStringContainsString('btnExportarPagoExcel', $view);
        $this->assertStringContainsString("confirmButtonText: 'Guardar'", $view);
        $this->assertStringContainsString("cancelButtonText: 'Cancelar'", $view);
        $this->assertStringContainsString('Ejemplo: 8 representa 8 horas.', $view);
        $this->assertStringContainsString('width: 768', $view);
        $this->assertTrue(Route::has('recursos-humanos.novedades-horario.export'));
        $this->assertTrue(Route::has('recursos-humanos.novedades-horario.export-pago'));
        $this->assertTrue(Route::has('recursos-humanos.novedades-horario.detalle'));
    }

    public function test_detail_filters_attendance_before_loading_rows(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/NovedadHorarioController.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString("TRIM(COALESCE(ab.cedula, '')) = ?", $controller);
        $this->assertStringContainsString("TRIM(COALESCE(an.identificacion, '')) = ?", $controller);
        $detailMethod = explode('private function validateCalculationRequest', explode('public function detalle', $controller)[1])[0];
        $this->assertStringNotContainsString('collectRows($request)', $detailMethod);
    }
}
