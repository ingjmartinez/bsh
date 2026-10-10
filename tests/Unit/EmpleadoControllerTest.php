<?php

namespace Tests\Unit;

use App\Http\Controllers\EmpleadoController;
use App\Http\Requests\SincronizarEmpleadosRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmpleadoControllerTest extends TestCase
{
    public function test_sincronizar_envia_los_filtros_requeridos_por_el_api(): void
    {
        Http::fake([
            'apisj.azurewebsites.net/*' => Http::response('Error simulado', 400),
        ]);

        $request = SincronizarEmpleadosRequest::create('/empleados/sincronizar', 'GET', [
            'empresa' => '126',
            'limite' => 2500,
        ]);

        $response = app(EmpleadoController::class)->sincronizar($request);

        $this->assertSame(502, $response->getStatusCode());

        Http::assertSent(function ($request): bool {
            return ($request->data()['intIdEmpresa'] ?? null) === '126'
                && (int) ($request->data()['intLimite'] ?? 0) === 2500
                && json_decode($request->data()['strFiltros'] ?? '', true) === [
                    ['CompanyId', '126'],
                ];
        });
    }

    public function test_sincronizar_valida_empresa_y_limite(): void
    {
        $reglas = (new SincronizarEmpleadosRequest)->rules();

        $casos = [
            [['empresa' => '126', 'limite' => 1], false],
            [['empresa' => '100', 'limite' => 10000], false],
            [['empresa' => '999', 'limite' => 10], true],
            [['empresa' => '126'], true],
            [['empresa' => '126', 'limite' => 0], true],
            [['empresa' => '126', 'limite' => 10001], true],
            [['empresa' => '126', 'limite' => 'abc'], true],
        ];

        foreach ($casos as [$datos, $debeFallar]) {
            $this->assertSame(
                $debeFallar,
                Validator::make($datos, $reglas)->fails(),
                json_encode($datos)
            );
        }
    }
}
