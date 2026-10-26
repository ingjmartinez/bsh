<?php

namespace Tests\Feature;

use App\Models\Agencia;
use App\Models\MonitoreoVentaAsignacion;
use App\Models\MonitoreoVentaCaso;
use App\Models\MonitoreoVentaUmbral;
use App\Models\User;
use App\Models\VtUsuarioBet;
use App\Services\MonitoreoVentasCmsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServicioGeneralMonitoreoVentasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
        });
        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });
        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('terminal');
            $table->string('nombre_agencia')->nullable();
            $table->string('central');
            $table->string('grupo');
            $table->string('ruta');
            $table->integer('estatus');
            $table->timestamps();
        });
        Schema::create('catalogo_juegos', function (Blueprint $table): void {
            $table->id();
            $table->integer('producto_id');
            $table->string('tipo')->nullable();
        });
        Schema::create('ventas_usuarios_bet', function (Blueprint $table): void {
            $table->id();
            $table->string('agencia_id');
            $table->integer('producto_id');
            $table->string('tipo')->nullable();
            $table->decimal('monto', 18, 2);
            $table->date('fecha');
            $table->timestamps();
        });
        foreach (glob(database_path('migrations/2026_10_20_*_create_monitoreo_venta_*_table.php')) as $migration) {
            (require $migration)->up();
        }
        (require database_path('migrations/2026_10_21_165338_create_monitoreo_venta_umbrals_table.php'))->up();
    }

    public function test_generates_only_below_threshold_and_does_not_duplicate_or_close_cases(): void
    {
        $asesor = User::query()->create(['name' => 'Asesor CMS', 'email' => 'asesor@example.test', 'password' => 'secret']);
        $agenciaCumple = Agencia::query()->create(['terminal' => '001', 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);
        $agenciaBaja = Agencia::query()->create(['terminal' => '002', 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);
        MonitoreoVentaAsignacion::query()->create(['central' => 'Central A', 'grupo' => 'Grupo A', 'asesor_id' => $asesor->id]);

        foreach ([$agenciaCumple->terminal => 5000, $agenciaBaja->terminal => 4999] as $terminal => $tradicional) {
            foreach ([['Tradicional', 10, $tradicional], ['No Tradicional', 11, 3000], ['Recarga', -1, 1000]] as [$tipo, $producto, $monto]) {
                VtUsuarioBet::query()->create(['agencia_id' => $terminal, 'producto_id' => $producto, 'tipo' => $tipo, 'monto' => $monto, 'fecha' => '2026-10-19']);
            }
        }

        $servicio = app(MonitoreoVentasCmsService::class);
        $this->assertSame(1, $servicio->generar(Carbon::parse('2026-10-19')));
        $caso = MonitoreoVentaCaso::query()->sole();
        $this->assertSame($agenciaBaja->id, $caso->agencia_id);
        $this->assertSame($asesor->id, $caso->asesor_id);
        $this->assertSame(1, $caso->seguimientos()->count());

        $this->assertSame(0, $servicio->generar(Carbon::parse('2026-10-19')));
        $this->assertSame(1, MonitoreoVentaCaso::query()->count());
        $caso->update(['estado' => 'cerrado', 'resultado' => 'Gestionado']);
        VtUsuarioBet::query()->where('agencia_id', $agenciaBaja->terminal)->where('producto_id', 10)->update(['monto' => 6000]);
        $servicio->generar(Carbon::parse('2026-10-19'));
        $this->assertSame('cerrado', $caso->fresh()->estado);
        $this->assertSame(4999.0, (float) $caso->fresh()->tradicional);
    }

    public function test_no_alerts_when_sales_have_not_been_loaded(): void
    {
        Agencia::query()->create(['terminal' => '003', 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);

        $this->assertSame(0, app(MonitoreoVentasCmsService::class)->generar(Carbon::parse('2026-10-19')));
        $this->assertSame(0, MonitoreoVentaCaso::query()->count());
    }

    public function test_comparativa_lists_assigned_terminals_that_meet_or_miss_each_product(): void
    {
        Gate::before(fn (): bool => true);
        $asesor = User::query()->create(['name' => 'Asesor CMS', 'email' => 'comparativa@example.test', 'password' => 'secret']);
        foreach (['010', '011', '012'] as $terminal) {
            Agencia::query()->create(['terminal' => $terminal, 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);
        }
        Agencia::query()->create(['terminal' => '013', 'central' => 'Central B', 'grupo' => 'Grupo B', 'ruta' => 'Ruta 2', 'estatus' => 1]);
        MonitoreoVentaAsignacion::query()->create(['central' => 'Central A', 'grupo' => 'Grupo A', 'asesor_id' => $asesor->id]);
        foreach ([['010', 5000], ['011', 3000], ['013', 6000]] as [$terminal, $monto]) {
            VtUsuarioBet::query()->create(['agencia_id' => $terminal, 'producto_id' => 10, 'tipo' => 'Tradicional', 'monto' => $monto, 'fecha' => '2026-10-19']);
        }

        $filas = app(MonitoreoVentasCmsService::class)->comparativaTerminales(
            Carbon::parse('2026-10-19'), MonitoreoVentaAsignacion::query()->with('asesor')->get(), collect()
        );
        $tradicional = $filas->firstWhere('categoria', 'tradicional');
        $this->assertSame(3, $tradicional['total']);
        $this->assertSame(['010'], $tradicional['cumplen']->pluck('terminal')->all());
        $this->assertSame(['011', '012'], $tradicional['pendientes']->pluck('terminal')->all());
        $this->actingAs($asesor)->get(route('servicios-generales.monitoreo-ventas.index'))
            ->assertOk()->assertSee('Comparativa de terminales por producto')->assertSee('011')->assertSee('012');
        $this->actingAs($asesor)->get(route('servicios-generales.monitoreo-ventas.index', ['fecha' => '2026-10-18']))
            ->assertOk()->assertSee('010')->assertSee('011')->assertSee('012')->assertSee('RD$ 0.00');
    }

    public function test_advisor_thresholds_control_alerts_and_are_saved_from_configuration(): void
    {
        Gate::before(fn (): bool => true);
        $asesor = User::query()->create(['name' => 'Asesor CMS', 'email' => 'asesor2@example.test', 'password' => 'secret']);
        Agencia::query()->create(['terminal' => '009', 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);
        MonitoreoVentaAsignacion::query()->create(['central' => 'Central A', 'grupo' => 'Grupo A', 'asesor_id' => $asesor->id]);
        foreach ([['Tradicional', 10, 4500], ['No Tradicional', 11, 3000], ['Recarga', -1, 1000]] as [$tipo, $producto, $monto]) {
            VtUsuarioBet::query()->create(['agencia_id' => '009', 'producto_id' => $producto, 'tipo' => $tipo, 'monto' => $monto, 'fecha' => '2026-10-19']);
        }

        $this->actingAs($asesor)->post(route('servicios-generales.monitoreo-ventas.umbrales'), [
            'asesor_id' => $asesor->id,
            'umbrales' => ['tradicional' => 4000, 'no_tradicional' => 3000, 'recargas' => 1000],
        ])->assertRedirect();
        $this->assertSame(4000.0, (float) MonitoreoVentaUmbral::query()->where('asesor_id', $asesor->id)->where('categoria', 'tradicional')->sole()->monto);
        $this->actingAs($asesor)->get(route('servicios-generales.monitoreo-ventas.index'))
            ->assertOk()->assertSee('Umbrales configurados (1)')->assertSee('Asesores agregados')->assertSee('Asesor CMS');
        $this->assertSame(0, app(MonitoreoVentasCmsService::class)->generar(Carbon::parse('2026-10-19')));

        $this->actingAs($asesor)->post(route('servicios-generales.monitoreo-ventas.umbrales'), [
            'asesor_id' => $asesor->id,
            'umbrales' => ['tradicional' => 5000, 'no_tradicional' => 3000, 'recargas' => 1000],
        ])->assertRedirect();
        $this->assertSame(1, app(MonitoreoVentasCmsService::class)->generar(Carbon::parse('2026-10-19')));
    }

    public function test_only_assigned_advisor_can_close_and_closure_requires_documentation(): void
    {
        Gate::before(fn (): bool => true);
        $asesor = User::query()->create(['name' => 'Asesor CMS', 'email' => 'asesor@example.test', 'password' => 'secret']);
        $supervisor = User::query()->create(['name' => 'Supervisor', 'email' => 'supervisor@example.test', 'password' => 'secret']);
        $agencia = Agencia::query()->create(['terminal' => '004', 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);
        $caso = MonitoreoVentaCaso::query()->create([
            'agencia_id' => $agencia->id, 'asesor_id' => $asesor->id, 'fecha' => Carbon::parse('2026-10-19'),
            'tradicional' => 0, 'no_tradicional' => 0, 'recargas' => 0,
        ]);
        $url = route('servicios-generales.monitoreo-ventas.seguimiento', $caso);

        $this->actingAs($asesor)->post($url, ['cerrar' => 1])->assertSessionHasErrors(['causa', 'gestion', 'resultado']);
        $this->assertSame('pendiente', $caso->fresh()->estado);

        $datos = ['causa' => 'Falta de conexión', 'gestion' => 'Se contactó a la agencia', 'resultado' => 'Servicio restablecido', 'cerrar' => 1];
        $this->actingAs($supervisor)->post($url, $datos)->assertForbidden();
        $this->actingAs($asesor)->post($url, $datos)->assertRedirect();
        $this->assertSame('cerrado', $caso->fresh()->estado);
        $this->assertSame($asesor->id, $caso->fresh()->cerrado_por);
        $this->assertSame(1, $caso->seguimientos()->count());
    }

    public function test_assignment_updates_open_cases_and_preserves_history(): void
    {
        Gate::before(fn (): bool => true);
        $supervisor = User::query()->create(['name' => 'Supervisor', 'email' => 'supervisor@example.test', 'password' => 'secret']);
        $asesor = User::query()->create(['name' => 'Asesor CMS', 'email' => 'asesor@example.test', 'password' => 'secret']);
        $agencia = Agencia::query()->create(['terminal' => '005', 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);
        $caso = MonitoreoVentaCaso::query()->create([
            'agencia_id' => $agencia->id, 'fecha' => Carbon::parse('2026-10-19'),
            'central' => 'Central A', 'grupo' => 'Grupo A', 'tradicional' => 0, 'no_tradicional' => 0, 'recargas' => 0,
        ]);

        $this->actingAs($supervisor)->post(route('servicios-generales.monitoreo-ventas.asignar'), [
            'central' => 'Central A', 'grupo' => 'Grupo A', 'asesor_id' => $asesor->id,
        ])->assertRedirect();

        $this->assertSame($asesor->id, $caso->fresh()->asesor_id);
        $this->assertSame('reasignada', $caso->seguimientos()->sole()->accion);
    }

    public function test_route_is_critical_with_two_distinct_agencies_alerted_on_same_day(): void
    {
        foreach (['006', '007', '008'] as $terminal) {
            $agencia = Agencia::query()->create(['terminal' => $terminal, 'central' => 'Central A', 'grupo' => 'Grupo A', 'ruta' => 'Ruta 1', 'estatus' => 1]);
            MonitoreoVentaCaso::query()->create([
                'agencia_id' => $agencia->id,
                'fecha' => Carbon::parse($terminal === '008' ? '2026-10-18' : '2026-10-19'),
                'ruta' => 'Ruta 1',
                'tradicional' => 0, 'no_tradicional' => 0, 'recargas' => 0,
            ]);
        }

        $servicio = app(MonitoreoVentasCmsService::class);
        $this->assertCount(1, $servicio->rutasCriticas(Carbon::parse('2026-10-19')));
        $this->assertSame(2, (int) $servicio->rutasCriticas(Carbon::parse('2026-10-19'))->first()->agencias_alerta);
        $this->assertCount(0, $servicio->rutasCriticas(Carbon::parse('2026-10-18')));
    }
}
