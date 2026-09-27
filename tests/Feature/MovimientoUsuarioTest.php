<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MovimientoUsuarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('empleados', function (Blueprint $table): void {
            $table->id();
            $table->string('cedula');
            $table->string('nombres');
            $table->string('apellidos');
            $table->integer('idcentrocosto')->nullable();
            $table->boolean('estatus')->default(true);
        });
        Schema::create('movimiento_usuarios', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('empleado_id');
            $table->string('cedula');
            $table->string('nombre_empleado');
            $table->string('terminal_origen')->nullable();
            $table->string('centro_costo_origen')->nullable();
            $table->string('grupo_origen')->nullable();
            $table->string('terminal_destino')->nullable();
            $table->string('centro_costo_destino')->nullable();
            $table->string('grupo_destino')->nullable();
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->timestamps();
        });

        DB::table('empleados')->insert([
            'id' => 10,
            'cedula' => '001-1234567-8',
            'nombres' => 'Junior',
            'apellidos' => 'Pérez',
            'idcentrocosto' => 250,
            'estatus' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('movimiento_usuarios');
        Schema::dropIfExists('empleados');

        parent::tearDown();
    }

    public function test_module_routes_card_and_view_are_registered(): void
    {
        $this->assertTrue(Route::has('recursos-humanos.movimiento-usuario.index'));
        $this->assertTrue(Route::has('recursos-humanos.movimiento-usuario.employee'));
        $this->assertTrue(Route::has('recursos-humanos.movimiento-usuario.store'));
        $this->assertTrue(Route::has('recursos-humanos.movimiento-usuario.list'));

        $config = file_get_contents(config_path('recursos_humanos.php'));
        $view = file_get_contents(resource_path('views/recursos_humanos/movimiento-usuario.blade.php'));
        $this->assertIsString($config);
        $this->assertIsString($view);
        $this->assertStringContainsString('/recursos-humanos/movimiento-usuario', $config);
        $this->assertStringContainsString('¿Dónde está actualmente?', $view);
        $this->assertStringContainsString('¿Hacia dónde se moverá?', $view);
        $this->assertStringContainsString('tablaMovimientos', $view);
        $this->assertStringContainsString("toLocaleString('es-DO'", $view);
        $this->assertStringContainsString('data-order=', $view);
        $this->assertStringContainsString('function clearEmployeeQuery()', $view);
        $this->assertStringContainsString('clearEmployeeQuery(); await loadHistory()', $view);
        $this->assertStringContainsString('Registrado por', $view);
        $this->assertStringContainsString('item.usuario_registro', $view);
    }

    public function test_employee_is_found_by_formatted_or_unformatted_identity(): void
    {
        $this->withoutMiddleware()
            ->getJson(route('recursos-humanos.movimiento-usuario.employee', ['cedula' => '00112345678']))
            ->assertOk()
            ->assertJsonPath('empleado.id', 10)
            ->assertJsonPath('empleado.nombre', 'Junior Pérez')
            ->assertJsonPath('empleado.centro_costo', 250);
    }

    public function test_movement_requires_origin_and_destination_and_can_be_stored(): void
    {
        $this->withoutMiddleware()
            ->postJson(route('recursos-humanos.movimiento-usuario.store'), ['empleado_id' => 10])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['terminal_origen', 'terminal_destino']);

        $user = new User(['name' => 'Administrador', 'email' => 'admin@example.com']);
        $user->id = 77;

        $this->actingAs($user)->withoutMiddleware()
            ->postJson(route('recursos-humanos.movimiento-usuario.store'), [
                'empleado_id' => 10,
                'terminal_origen' => '1001',
                'centro_costo_destino' => '300',
                'grupo_destino' => 'Grupo Norte',
                'observacion' => 'Movimiento de primera fase.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Movimiento registrado correctamente.');

        $this->assertDatabaseHas('movimiento_usuarios', [
            'empleado_id' => 10,
            'cedula' => '001-1234567-8',
            'nombre_empleado' => 'Junior Pérez',
            'terminal_origen' => '1001',
            'centro_costo_destino' => '300',
            'grupo_destino' => 'Grupo Norte',
            'registrado_por' => 77,
        ]);
    }
}
