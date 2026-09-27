<?php

namespace Tests\Feature;

use App\Http\Controllers\ReporteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ReporteCruceUsuariosTest extends TestCase
{
    public function test_user_cross_report_uses_current_employee_exit_date_column(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->withArgs(fn (string $query, array $bindings): bool => str_contains($query, 'e.fecha_egreso')
                && ! str_contains($query, 'e.fechasalida')
                && $bindings === ['2026-09-01', '2026-09-26'])
            ->andReturn([(object) [
                'Identificacion' => '00112345678',
                'Empleado_ID' => 10,
                'NombreCompleto' => 'Empleado Inactivo',
                'Detalle' => 'Agencia(s): 1001',
                'Estatus' => 'No Activo - 2026-09-10',
                'Ultima_Fecha_Venta' => '2026-09-20',
            ]]);

        DB::shouldReceive('select')
            ->once()
            ->withArgs(fn (string $query, array $bindings): bool => str_contains($query, 'Dias_Sin_Cedula_Con_Ventas')
                && $bindings === ['2026-09-01', '2026-09-26'])
            ->andReturn([]);

        $request = Request::create('/reportes-cruce-usuarios/list', 'GET', [
            'sistema' => 'Lotobet',
            'fecha_inicio' => '2026-09-01',
            'fecha_fin' => '2026-09-26',
        ]);

        $response = app(ReporteController::class)->listCruceUsuarios($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('No Activo - 2026-09-10', $response->getData(true)['resultados'][0]['Estatus']);
    }

    public function test_view_handles_ajax_errors_without_datatables_native_alert(): void
    {
        $view = file_get_contents(resource_path('views/reportes/cruce-usuarios.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString("$.fn.dataTable.ext.errMode = 'none'", $view);
        $this->assertStringContainsString('Error al cargar el reporte', $view);
    }

    public function test_user_cross_report_belongs_to_human_resources_module(): void
    {
        $this->assertTrue(Route::has('recursos-humanos.cruce-usuarios.index'));
        $this->assertTrue(Route::has('recursos-humanos.cruce-usuarios.list'));
        $this->assertTrue(Route::has('recursos-humanos.cruce-usuarios.sin-cedula-fechas'));

        $humanResourcesReport = collect(config('recursos_humanos'))
            ->firstWhere('url', '/recursos-humanos/cruce-usuarios');
        $legacyReport = collect(config('reportes'))
            ->firstWhere('url', '/reportes-cruce-usuarios');

        $this->assertNotNull($humanResourcesReport);
        $this->assertTrue($humanResourcesReport['activo']);
        $this->assertNotNull($legacyReport);
        $this->assertFalse($legacyReport['activo']);

        $view = file_get_contents(resource_path('views/reportes/cruce-usuarios.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString("route('recursos-humanos.index')", $view);
        $this->assertStringContainsString("route('recursos-humanos.cruce-usuarios.list')", $view);
        $this->assertStringContainsString("route('recursos-humanos.cruce-usuarios.sin-cedula-fechas')", $view);
        $this->assertStringNotContainsString("route('reportes.index')", $view);
    }

    public function test_legacy_user_cross_report_url_redirects_to_human_resources(): void
    {
        $this->withoutMiddleware()
            ->get('/reportes-cruce-usuarios')
            ->assertRedirect('/recursos-humanos/cruce-usuarios');
    }
}
