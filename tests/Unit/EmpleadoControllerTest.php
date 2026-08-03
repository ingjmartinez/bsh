<?php

namespace Tests\Unit;

use App\Http\Controllers\EmpleadoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmpleadoControllerTest extends TestCase
{
    public function test_sincronizar_envia_los_filtros_requeridos_por_el_api(): void
    {
        Http::fake([
            'apisj.azurewebsites.net/*' => Http::response('Error simulado', 400),
        ]);

        $request = Request::create('/empleados/sincronizar', 'GET', [
            'empresa' => '126',
        ]);

        $response = app(EmpleadoController::class)->sincronizar($request);

        $this->assertSame(502, $response->getStatusCode());

        Http::assertSent(function ($request): bool {
            return ($request->data()['intIdEmpresa'] ?? null) === '126'
                && json_decode($request->data()['strFiltros'] ?? '', true) === [
                    ['CompanyId', '126'],
                ];
        });
    }
}
