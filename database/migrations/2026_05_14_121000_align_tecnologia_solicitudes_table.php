<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('tecnologia_solicitudes', 'prioridad')) {
                $table->string('prioridad', 20)->default('media')->after('descripcion');
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'detalle_solucion')) {
                $table->text('detalle_solucion')->nullable()->after('progreso');
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'asignado_at')) {
                $table->timestamp('asignado_at')->nullable()->after('asignado_a_id');
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'cerrado_por_id')) {
                $table->foreignId('cerrado_por_id')
                    ->nullable()
                    ->after('fecha_completada')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        DB::table('tipos_solicitud_tecnologia')->updateOrInsert(
            ['nombre' => 'Averia'],
            [
                'activo' => true,
                'requiere_progreso' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('tipos_solicitud_tecnologia')->updateOrInsert(
            ['nombre' => 'Desarrollo'],
            [
                'activo' => true,
                'requiere_progreso' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('tecnologia_solicitudes', 'cerrado_por_id')) {
                $table->dropConstrainedForeignId('cerrado_por_id');
            }

            if (Schema::hasColumn('tecnologia_solicitudes', 'asignado_at')) {
                $table->dropColumn('asignado_at');
            }

            if (Schema::hasColumn('tecnologia_solicitudes', 'detalle_solucion')) {
                $table->dropColumn('detalle_solucion');
            }

            if (Schema::hasColumn('tecnologia_solicitudes', 'prioridad')) {
                $table->dropColumn('prioridad');
            }
        });
    }
};
