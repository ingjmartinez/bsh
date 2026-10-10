<?php

namespace Tests\Feature;

use App\Http\Controllers\EmpleadoController;
use App\Http\Requests\SincronizarEmpleadosRequest;
use App\Models\Empleado;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmpleadoSincronizacionSalarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('empleados', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('companyid');
            $table->unsignedInteger('empleadoid');
            $table->unsignedInteger('idcentrocosto')->nullable();
            $table->string('cedula')->nullable();
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_egreso')->nullable();
            $table->boolean('estatus')->nullable();
            $table->decimal('salario', 12, 2)->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('tipo_cuenta')->nullable();
            $table->string('fuente_sync')->nullable();
            $table->dateTime('ultima_sync_at')->nullable();
            $table->timestamps();
            $table->unique(['companyid', 'empleadoid']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('empleados');

        parent::tearDown();
    }

    public function test_sincronizar_no_borra_el_salario_si_el_api_no_lo_envia(): void
    {
        Empleado::query()->insert([
            'companyid' => 126,
            'empleadoid' => 1,
            'nombres' => 'Antes',
            'salario' => 13685,
        ]);

        Http::fake([
            'apisj.azurewebsites.net/*' => Http::response([
                ['COMPANYID' => 126, 'EMPLEADOID' => 1, 'NOMBRES' => 'Despues', 'APELLIDOS' => 'Perez', 'CEDULA' => '00100000001'],
            ]),
        ]);

        $response = app(EmpleadoController::class)->sincronizar($this->solicitud());

        $this->assertSame(200, $response->getStatusCode());

        $empleado = Empleado::query()->where('empleadoid', 1)->first();
        $this->assertSame('Despues', $empleado->nombres);
        $this->assertEquals(13685, $empleado->salario);
    }

    public function test_sincronizar_actualiza_el_salario_si_el_api_lo_envia(): void
    {
        Http::fake([
            'apisj.azurewebsites.net/*' => Http::response([
                ['COMPANYID' => 126, 'EMPLEADOID' => 2, 'NOMBRES' => 'Ana', 'SALARIOMENSUAL' => 15000],
            ]),
        ]);

        app(EmpleadoController::class)->sincronizar($this->solicitud());

        $this->assertEquals(15000, Empleado::query()->where('empleadoid', 2)->value('salario'));
    }

    public function test_sincronizar_solo_actualiza_los_empleados_con_cambios(): void
    {
        $registro = [
            'COMPANYID' => 126,
            'EMPLEADOID' => 5,
            'NOMBRES' => 'Lucila',
            'APELLIDOS' => 'Perez',
            'CEDULA' => '05500191514',
            'FECHAINGRESO' => '2016-09-01T00:00:00',
            'SALARIOMENSUAL' => 12000,
        ];

        $primera = $this->sincronizarCon([$registro]);
        $this->assertSame(1, $primera['nuevos']);

        Empleado::query()->where('empleadoid', 5)->update([
            'updated_at' => '2026-01-01 00:00:00',
            'ultima_sync_at' => '2026-01-01 00:00:00',
        ]);

        $sinCambios = $this->sincronizarCon([$registro]);

        $this->assertSame(0, $sinCambios['nuevos']);
        $this->assertSame(0, $sinCambios['actualizados']);
        $this->assertSame(1, $sinCambios['sin_cambios']);
        $this->assertSame('2026-01-01 00:00:00', (string) Empleado::query()->where('empleadoid', 5)->value('updated_at'));

        $conCambios = $this->sincronizarCon([array_merge($registro, ['APELLIDOS' => 'Gomez'])]);

        $this->assertSame(1, $conCambios['actualizados']);
        $this->assertSame(0, $conCambios['sin_cambios']);
        $this->assertSame('Gomez', Empleado::query()->where('empleadoid', 5)->value('apellidos'));
        $this->assertNotSame('2026-01-01 00:00:00', (string) Empleado::query()->where('empleadoid', 5)->value('updated_at'));
    }

    public function test_sincronizar_separa_nuevos_actualizados_y_sin_cambios_en_el_mismo_lote(): void
    {
        $base = ['COMPANYID' => 126, 'NOMBRES' => 'Ana', 'APELLIDOS' => 'Diaz'];

        $this->sincronizarCon([
            array_merge($base, ['EMPLEADOID' => 1]),
            array_merge($base, ['EMPLEADOID' => 2]),
        ]);

        $resumen = $this->sincronizarCon([
            array_merge($base, ['EMPLEADOID' => 1]),
            array_merge($base, ['EMPLEADOID' => 2, 'NOMBRES' => 'Ana Maria']),
            array_merge($base, ['EMPLEADOID' => 3]),
        ]);

        $this->assertSame(1, $resumen['nuevos']);
        $this->assertSame(1, $resumen['actualizados']);
        $this->assertSame(1, $resumen['sin_cambios']);
        $this->assertSame(3, Empleado::query()->count());
    }

    /**
     * @param  array<int, array<string, mixed>>  $registros
     * @return array<string, mixed>
     */
    private function sincronizarCon(array $registros): array
    {
        Http::swap(new HttpFactory);
        Http::fake(['apisj.azurewebsites.net/*' => Http::response($registros)]);

        return app(EmpleadoController::class)->sincronizar($this->solicitud())->getData(true);
    }

    private function solicitud(): SincronizarEmpleadosRequest
    {
        return SincronizarEmpleadosRequest::create('/empleados/sincronizar', 'GET', [
            'empresa' => '126',
            'limite' => 10,
        ]);
    }
}
