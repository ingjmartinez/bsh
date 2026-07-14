<?php

namespace Tests\Feature;

use App\Http\Controllers\ServicioGeneralRutaInspeccionController;
use App\Http\Controllers\CoordinadorOperadorController;
use App\Models\CoordinadorOperador;
use App\Models\ServicioGeneralRutaInspeccion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServicioGeneralRutaInspeccionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('agencias', function (Blueprint $table) {
            $table->id();
            $table->string('terminal')->nullable();
            $table->string('codigo')->nullable();
            $table->string('nombre')->nullable();
            $table->timestamps();
        });
        Schema::create('coordinadores_operador', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido')->nullable();
            $table->string('cargo')->nullable();
            $table->timestamps();
        });
        Schema::create('coordinador_operador_agencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coordinador_operador_id');
            $table->unsignedBigInteger('agencia_id');
            $table->timestamps();
            $table->unique('agencia_id');
        });
        Schema::create('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('agencia_id')->nullable();
            $table->unsignedBigInteger('coordinador_operador_id')->nullable();
            $table->string('responsable_nombre')->nullable();
            $table->string('tipo')->default('inspeccion');
            $table->string('nombre')->nullable();
            $table->date('fecha')->nullable();
            $table->string('estado')->default('asignada');
            $table->string('prioridad')->default('media');
            $table->text('descripcion')->nullable();
            $table->text('detalle_solucion')->nullable();
            $table->string('evidencia_path')->nullable();
            $table->timestamp('iniciado_at')->nullable();
            $table->timestamp('cierre_solicitado_at')->nullable();
            $table->timestamp('cerrado_at')->nullable();
            $table->unsignedBigInteger('cerrado_por')->nullable();
            $table->json('metadata')->nullable();
            $table->string('clave_generacion')->nullable()->unique();
            $table->boolean('generado_automaticamente')->default(false);
            $table->unsignedBigInteger('visita_origen_id')->nullable();
            $table->unsignedBigInteger('checklist_item_id')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_in_latitud', 10, 7)->nullable();
            $table->decimal('check_in_longitud', 10, 7)->nullable();
            $table->unsignedTinyInteger('cumplimiento_porcentaje')->default(0);
            $table->boolean('conforme')->nullable();
            $table->timestamps();
        });
        Schema::create('servicios_generales_rutas_inspeccion_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruta_inspeccion_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion');
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo')->nullable();
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->string('responsable_nombre')->nullable();
            $table->text('observacion')->nullable();
            $table->json('cambios')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('servicios_generales_rutas_inspeccion_historial');
        Schema::dropIfExists('servicios_generales_rutas_inspeccion');
        Schema::dropIfExists('coordinador_operador_agencia');
        Schema::dropIfExists('coordinadores_operador');
        Schema::dropIfExists('agencias');

        parent::tearDown();
    }

    public function test_trabajo_conserva_responsable_historico_y_nuevo_trabajo_usa_responsable_vigente(): void
    {
        $juniorId = DB::table('coordinadores_operador')->insertGetId([
            'nombre' => 'Junior', 'apellido' => 'Pérez', 'cargo' => 'Lider De Zona',
        ]);
        $pedroId = DB::table('coordinadores_operador')->insertGetId([
            'nombre' => 'Pedro', 'apellido' => 'Díaz', 'cargo' => 'Lider De Zona',
        ]);
        $agenciaId = DB::table('agencias')->insertGetId([
            'terminal' => 'T-001', 'codigo' => '001', 'nombre' => 'Agencia Centro',
        ]);
        DB::table('coordinador_operador_agencia')->insert([
            'coordinador_operador_id' => $juniorId, 'agencia_id' => $agenciaId,
        ]);

        $controller = app(ServicioGeneralRutaInspeccionController::class);
        $controller->store(Request::create('/servicios-generales/ruta-inspeccion', 'POST', [
            'agencia_id' => $agenciaId,
            'tipo' => 'averia',
            'fecha' => '2026-07-13',
            'prioridad' => 'alta',
            'descripcion' => 'Falla eléctrica.',
        ]));

        $primerTrabajo = ServicioGeneralRutaInspeccion::firstOrFail();
        $this->assertSame($juniorId, (int) $primerTrabajo->coordinador_operador_id);
        $this->assertSame('Junior Pérez', $primerTrabajo->responsable_nombre);

        DB::table('coordinador_operador_agencia')->where('agencia_id', $agenciaId)->delete();
        DB::table('coordinador_operador_agencia')->insert([
            'coordinador_operador_id' => $pedroId, 'agencia_id' => $agenciaId,
        ]);

        $controller->store(Request::create('/servicios-generales/ruta-inspeccion', 'POST', [
            'agencia_id' => $agenciaId,
            'tipo' => 'inspeccion',
            'fecha' => '2026-07-14',
            'prioridad' => 'media',
            'descripcion' => 'Inspección programada.',
        ]));

        $this->assertSame($juniorId, (int) $primerTrabajo->fresh()->coordinador_operador_id);
        $this->assertSame($pedroId, (int) ServicioGeneralRutaInspeccion::latest('id')->first()->coordinador_operador_id);
        $this->assertDatabaseCount('servicios_generales_rutas_inspeccion_historial', 2);
    }

    public function test_generacion_diaria_es_automatica_y_no_duplica_visitas(): void
    {
        $responsableId = DB::table('coordinadores_operador')->insertGetId([
            'nombre' => 'Junior', 'apellido' => 'Pérez', 'cargo' => 'Lider De Zona',
        ]);
        $agenciaId = DB::table('agencias')->insertGetId([
            'terminal' => 'T-001', 'codigo' => '001', 'nombre' => 'Agencia Centro',
        ]);
        DB::table('coordinador_operador_agencia')->insert([
            'coordinador_operador_id' => $responsableId, 'agencia_id' => $agenciaId,
        ]);

        $this->artisan('supervision:generar-visitas', ['--fecha' => '2026-07-15'])->assertSuccessful();
        $this->artisan('supervision:generar-visitas', ['--fecha' => '2026-07-15'])->assertSuccessful();

        $this->assertDatabaseCount('servicios_generales_rutas_inspeccion', 1);
        $this->assertDatabaseHas('servicios_generales_rutas_inspeccion', [
            'agencia_id' => $agenciaId,
            'coordinador_operador_id' => $responsableId,
            'generado_automaticamente' => 1,
        ]);
        $this->assertSame('2026-07-15', ServicioGeneralRutaInspeccion::firstOrFail()->fecha->toDateString());
    }

    public function test_tablero_separa_agencias_visitadas_y_no_visitadas_por_responsable(): void
    {
        $responsableId = DB::table('coordinadores_operador')->insertGetId(['nombre'=>'Junior','apellido'=>'Pérez','cargo'=>'Lider De Zona']);
        $agenciaId = DB::table('agencias')->insertGetId(['terminal'=>'T-001','codigo'=>'001','nombre'=>'Agencia Centro']);
        DB::table('coordinador_operador_agencia')->insert(['coordinador_operador_id'=>$responsableId,'agencia_id'=>$agenciaId]);
        $this->artisan('supervision:generar-visitas', ['--fecha'=>'2026-07-16'])->assertSuccessful();

        $controller = app(ServicioGeneralRutaInspeccionController::class);
        $pendiente = $controller->index(Request::create('/servicios-generales/ruta-inspeccion','GET',['fecha'=>'2026-07-16']))->getData();
        $this->assertSame(0, $pendiente['statsRuta']['visitadas']);
        $this->assertSame(1, $pendiente['statsRuta']['pendientes']);

        ServicioGeneralRutaInspeccion::firstOrFail()->update(['check_out_at'=>now(),'estado'=>'cerrada','conforme'=>true]);
        $visitada = $controller->index(Request::create('/servicios-generales/ruta-inspeccion','GET',['fecha'=>'2026-07-16']))->getData();
        $this->assertSame(1, $visitada['statsRuta']['visitadas']);
        $this->assertSame(0, $visitada['statsRuta']['pendientes']);
    }

    public function test_una_agencia_no_puede_pertenecer_a_dos_responsables(): void
    {
        $juniorId = DB::table('coordinadores_operador')->insertGetId(['nombre'=>'Junior','cargo'=>'Lider De Zona']);
        $pedroId = DB::table('coordinadores_operador')->insertGetId(['nombre'=>'Pedro','cargo'=>'Lider De Zona']);
        $agenciaId = DB::table('agencias')->insertGetId(['terminal'=>'T-001','codigo'=>'001']);
        DB::table('coordinador_operador_agencia')->insert(['coordinador_operador_id'=>$juniorId,'agencia_id'=>$agenciaId]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('coordinador_operador_agencia')->insert(['coordinador_operador_id'=>$pedroId,'agencia_id'=>$agenciaId]);
    }

    public function test_reasignar_una_agencia_la_mueve_del_responsable_anterior(): void
    {
        Schema::create('coordinador_operador_agencia_historial', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('agencia_id')->nullable();
            $table->unsignedBigInteger('responsable_anterior_id')->nullable(); $table->string('responsable_anterior_nombre')->nullable();
            $table->unsignedBigInteger('responsable_nuevo_id')->nullable(); $table->string('responsable_nuevo_nombre')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); $table->text('motivo'); $table->json('metadata')->nullable(); $table->timestamp('created_at')->nullable();
        });
        $juniorId = DB::table('coordinadores_operador')->insertGetId(['nombre'=>'Junior','apellido'=>'Pérez','cargo'=>'Lider De Zona']);
        $pedroId = DB::table('coordinadores_operador')->insertGetId(['nombre'=>'Pedro','apellido'=>'Díaz','cargo'=>'Lider De Zona']);
        $agenciaId = DB::table('agencias')->insertGetId(['terminal'=>'T-001','codigo'=>'001']);
        DB::table('coordinador_operador_agencia')->insert(['coordinador_operador_id'=>$juniorId,'agencia_id'=>$agenciaId]);

        app(CoordinadorOperadorController::class)->asignarAgencias(
            Request::create('/coordinador-operador/'.$pedroId.'/asignar-agencias','POST',[
                'agencias'=>[$agenciaId], 'confirmar_reasignacion'=>1,
            ]),
            CoordinadorOperador::findOrFail($pedroId)
        );

        $this->assertDatabaseCount('coordinador_operador_agencia', 1);
        $this->assertDatabaseHas('coordinador_operador_agencia', ['agencia_id'=>$agenciaId,'coordinador_operador_id'=>$pedroId]);
        $this->assertDatabaseHas('coordinador_operador_agencia_historial', [
            'agencia_id'=>$agenciaId,'responsable_anterior_id'=>$juniorId,'responsable_nuevo_id'=>$pedroId,
            'motivo'=>'Traslado de agencia confirmado desde Coordinador/Operador.',
        ]);
        Schema::dropIfExists('coordinador_operador_agencia_historial');
    }
}
