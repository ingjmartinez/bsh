<?php

namespace Tests\Feature;

use App\Http\Controllers\AgenciaLotedomController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgenciaLotedomControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('agencias_lotedom');
        Schema::create('agencias_lotedom', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia', 25)->nullable();
            $table->string('codigo', 25)->nullable();
            $table->string('nombre_agencia', 55)->nullable();
            $table->string('nombre', 55)->nullable();
            $table->string('terminal', 25)->nullable()->unique();
            $table->string('horario_am', 35)->nullable();
            $table->string('horario_pm', 35)->nullable();
            $table->string('sistema', 55)->nullable();
            $table->string('empresa', 60)->nullable();
            $table->string('ciudad', 55)->nullable();
            $table->string('ruta', 55)->nullable();
            $table->string('operador', 55)->nullable();
            $table->string('coordinador', 55)->nullable();
            $table->tinyInteger('estatus')->default(1);
            $table->boolean('aplica_incentivo')->default(true);
            $table->timestamps();
        });

        Schema::dropIfExists('ventas_usuarios_net');
        Schema::create('ventas_usuarios_net', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('consorcio_id')->nullable();
            $table->string('agencia_id', 25)->nullable()->index();
            $table->string('cedula', 20)->nullable();
            $table->integer('producto_id')->nullable();
            $table->string('descripcion', 120)->nullable();
            $table->string('tipo', 60)->nullable();
            $table->decimal('monto', 14, 2)->default(0);
            $table->date('fecha')->nullable()->index();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('ventas_usuarios_net');
        Schema::dropIfExists('agencias_lotedom');

        parent::tearDown();
    }

    public function test_sincroniza_no_registradas_desde_ventas_por_usuario_lotedom(): void
    {
        DB::table('agencias_lotedom')->insert([
            'agencia' => '1002',
            'codigo' => '1002',
            'terminal' => '1002',
            'sistema' => 'lotedom',
            'estatus' => 1,
            'aplica_incentivo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ventas_usuarios_net')->insert([
            [
                'agencia_id' => '1001',
                'monto' => 150.50,
                'fecha' => '2026-06-20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agencia_id' => '1001',
                'monto' => 90.00,
                'fecha' => '2026-06-21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agencia_id' => '1002',
                'monto' => 75.00,
                'fecha' => '2026-06-21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $controller = new AgenciaLotedomController();

        $pendientesAntes = $controller->noRegistradasDesdeVentasUsuarios(new Request());
        $payloadAntes = $pendientesAntes->getData(true);

        $this->assertTrue($payloadAntes['ok']);
        $this->assertSame(1, $payloadAntes['total']);
        $this->assertSame('1001', $payloadAntes['terminales'][0]['terminal']);

        $sync = $controller->sincronizarNoRegistradasDesdeVentasUsuarios(new Request());
        $payloadSync = $sync->getData(true);

        $this->assertTrue($payloadSync['ok']);
        $this->assertSame(1, $payloadSync['registradas']);
        $this->assertSame(1, $payloadSync['total_solicitadas']);
        $this->assertDatabaseHas('agencias_lotedom', [
            'terminal' => '1001',
            'agencia' => '1001',
            'sistema' => 'lotedom',
        ]);

        $pendientesDespues = $controller->noRegistradasDesdeVentasUsuarios(new Request());
        $payloadDespues = $pendientesDespues->getData(true);

        $this->assertTrue($payloadDespues['ok']);
        $this->assertSame(0, $payloadDespues['total']);
    }
}
