<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
            $table->string('clave_generacion', 100)->nullable()->unique('sg_visita_clave_unique');
            $table->boolean('generado_automaticamente')->default(false)->index('sg_visita_auto_idx');
            $table->unsignedBigInteger('visita_origen_id')->nullable()->index('sg_visita_origen_idx');
            $table->unsignedBigInteger('checklist_item_id')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_in_latitud', 10, 7)->nullable();
            $table->decimal('check_in_longitud', 10, 7)->nullable();
            $table->unsignedTinyInteger('cumplimiento_porcentaje')->default(0);
            $table->boolean('conforme')->nullable();
            $table->unique(['visita_origen_id', 'checklist_item_id'], 'sg_averia_visita_item_unique');
        });

        Schema::create('servicios_generales_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 180);
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('requerido')->default(true);
            $table->boolean('requiere_evidencia_fallo')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['activo', 'orden'], 'sg_checklist_activo_orden_idx');
        });

        Schema::create('servicios_generales_visita_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_inspeccion_id')->constrained('servicios_generales_rutas_inspeccion')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('servicios_generales_checklist_items')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resultado', 20);
            $table->text('observacion')->nullable();
            $table->string('evidencia_path', 500)->nullable();
            $table->timestamps();
            $table->unique(['ruta_inspeccion_id', 'checklist_item_id'], 'sg_visita_respuesta_unique');
        });

        $ahora = now();
        DB::table('servicios_generales_checklist_items')->insert([
            ['nombre' => 'Sistema en buen estado', 'descripcion' => 'El sistema inicia, permite acceso y trabaja sin errores.', 'orden' => 10, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Internet estable', 'descripcion' => 'La agencia tiene conexión estable y puede operar.', 'orden' => 20, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Printer trabajando correctamente', 'descripcion' => 'La impresora está encendida y completa una impresión de prueba.', 'orden' => 30, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Pantallas de sorteos funcionando', 'descripcion' => 'Las pantallas están encendidas, con señal e imagen correctas.', 'orden' => 40, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Inversor en buen estado', 'descripcion' => 'El inversor enciende y responde correctamente.', 'orden' => 50, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Energía eléctrica estable', 'descripcion' => 'No existen fallos eléctricos visibles durante la visita.', 'orden' => 60, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Equipos y cableado organizados', 'descripcion' => 'Equipos, conectores y cables están seguros y en buen estado.', 'orden' => 70, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Área limpia y organizada', 'descripcion' => 'El área de servicio cumple las condiciones de orden y limpieza.', 'orden' => 80, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);

        foreach (['coordinador_operador', 'coordinadores_operador'] as $tabla) {
            if (!Schema::hasTable($tabla) || Schema::hasColumn($tabla, 'user_id')) continue;
            Schema::table($tabla, fn(Blueprint $table) => $table->unsignedBigInteger('user_id')->nullable()->unique());
            $correo = Schema::hasColumn($tabla, 'email') ? 'email' : (Schema::hasColumn($tabla, 'correo') ? 'correo' : null);
            if ($correo) {
                DB::table($tabla)->orderBy('id')->each(function ($persona) use ($tabla, $correo) {
                    $userId = DB::table('users')->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $persona->{$correo})])->value('id');
                    if ($userId && !DB::table($tabla)->where('user_id', $userId)->exists()) DB::table($tabla)->where('id', $persona->id)->update(['user_id' => $userId]);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_generales_visita_respuestas');
        Schema::dropIfExists('servicios_generales_checklist_items');
    }
};
