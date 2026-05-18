<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tecnologia_solicitudes')) {
            return;
        }

        $this->ensureTipos();

        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('tecnologia_solicitudes', 'solicitante_id')) {
                $table->unsignedBigInteger('solicitante_id')->nullable()->index();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'asignado_a_id')) {
                $table->unsignedBigInteger('asignado_a_id')->nullable()->index();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'tipo_solicitud_id')) {
                $table->unsignedBigInteger('tipo_solicitud_id')->nullable()->index();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'prioridad')) {
                $table->string('prioridad', 20)->default('media');
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'progreso')) {
                $table->unsignedTinyInteger('progreso')->default(0);
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'detalle_solucion')) {
                $table->text('detalle_solucion')->nullable();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'cierre_solicitado_at')) {
                $table->timestamp('cierre_solicitado_at')->nullable();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'cierre_solicitado_por_id')) {
                $table->unsignedBigInteger('cierre_solicitado_por_id')->nullable()->index();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'asignado_at')) {
                $table->timestamp('asignado_at')->nullable();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'fecha_completada')) {
                $table->date('fecha_completada')->nullable();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'cerrado_por_id')) {
                $table->unsignedBigInteger('cerrado_por_id')->nullable()->index();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'adjunto_path')) {
                $table->string('adjunto_path')->nullable();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'adjunto_nombre')) {
                $table->string('adjunto_nombre')->nullable();
            }
        });

        $this->copyLegacyColumns();
    }

    public function down(): void
    {
        if (!Schema::hasTable('tecnologia_solicitudes')) {
            return;
        }

        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            foreach ([
                'adjunto_nombre',
                'adjunto_path',
                'cerrado_por_id',
                'fecha_completada',
                'asignado_at',
                'cierre_solicitado_por_id',
                'cierre_solicitado_at',
                'detalle_solucion',
                'progreso',
                'prioridad',
                'tipo_solicitud_id',
                'asignado_a_id',
                'solicitante_id',
            ] as $column) {
                if (Schema::hasColumn('tecnologia_solicitudes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function ensureTipos(): void
    {
        if (!Schema::hasTable('tipos_solicitud_tecnologia')) {
            Schema::create('tipos_solicitud_tecnologia', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 80)->unique();
                $table->boolean('activo')->default(true);
                $table->boolean('requiere_progreso')->default(false);
                $table->timestamps();
            });
        }

        DB::table('tipos_solicitud_tecnologia')->updateOrInsert(
            ['nombre' => 'Averia'],
            ['activo' => true, 'requiere_progreso' => false, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('tipos_solicitud_tecnologia')->updateOrInsert(
            ['nombre' => 'Desarrollo'],
            ['activo' => true, 'requiere_progreso' => true, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function copyLegacyColumns(): void
    {
        if (Schema::hasColumn('tecnologia_solicitudes', 'user_id')) {
            DB::statement('UPDATE tecnologia_solicitudes SET solicitante_id = COALESCE(solicitante_id, user_id)');
        }

        if (Schema::hasColumn('tecnologia_solicitudes', 'asignado_id')) {
            DB::statement('UPDATE tecnologia_solicitudes SET asignado_a_id = COALESCE(asignado_a_id, asignado_id)');
        }

        if (Schema::hasColumn('tecnologia_solicitudes', 'cierre_solicitado_por')) {
            DB::statement('UPDATE tecnologia_solicitudes SET cierre_solicitado_por_id = COALESCE(cierre_solicitado_por_id, cierre_solicitado_por)');
        }

        if (Schema::hasColumn('tecnologia_solicitudes', 'cerrado_por')) {
            DB::statement('UPDATE tecnologia_solicitudes SET cerrado_por_id = COALESCE(cerrado_por_id, cerrado_por)');
        }

        if (Schema::hasColumn('tecnologia_solicitudes', 'resuelto_at')) {
            DB::statement('UPDATE tecnologia_solicitudes SET fecha_completada = COALESCE(fecha_completada, DATE(resuelto_at)) WHERE resuelto_at IS NOT NULL');
        }

        if (Schema::hasColumn('tecnologia_solicitudes', 'tipo')) {
            $averiaId = DB::table('tipos_solicitud_tecnologia')->where('nombre', 'Averia')->value('id');
            $desarrolloId = DB::table('tipos_solicitud_tecnologia')->where('nombre', 'Desarrollo')->value('id');

            DB::table('tecnologia_solicitudes')
                ->whereNull('tipo_solicitud_id')
                ->whereRaw('LOWER(tipo) = ?', ['desarrollo'])
                ->update(['tipo_solicitud_id' => $desarrolloId]);

            DB::table('tecnologia_solicitudes')
                ->whereNull('tipo_solicitud_id')
                ->update(['tipo_solicitud_id' => $averiaId]);
        }
    }
};
