<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('servicios_generales_rutas_inspeccion')) {
            Schema::create('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('nombre', 160)->nullable();
                $table->date('fecha')->nullable();
                $table->string('estado', 30)->default('asignada');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'agencia_id')) {
                $table->foreignId('agencia_id')->nullable()->after('user_id')->constrained('agencias')->nullOnDelete();
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'coordinador_operador_id')) {
                // La tabla de coordinadores tiene nombres heredados distintos según la instalación.
                $table->unsignedBigInteger('coordinador_operador_id')->nullable()->after('agencia_id');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'responsable_nombre')) {
                $table->string('responsable_nombre', 200)->nullable()->after('coordinador_operador_id');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'tipo')) {
                $table->string('tipo', 20)->default('inspeccion')->after('responsable_nombre');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'prioridad')) {
                $table->string('prioridad', 20)->default('media')->after('estado');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('prioridad');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'detalle_solucion')) {
                $table->text('detalle_solucion')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'evidencia_path')) {
                $table->string('evidencia_path', 500)->nullable()->after('detalle_solucion');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'iniciado_at')) {
                $table->timestamp('iniciado_at')->nullable()->after('evidencia_path');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'cierre_solicitado_at')) {
                $table->timestamp('cierre_solicitado_at')->nullable()->after('iniciado_at');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'cerrado_at')) {
                $table->timestamp('cerrado_at')->nullable()->after('cierre_solicitado_at');
            }
            if (!Schema::hasColumn('servicios_generales_rutas_inspeccion', 'cerrado_por')) {
                $table->foreignId('cerrado_por')->nullable()->after('cerrado_at')->constrained('users')->nullOnDelete();
            }
        });

        if (!Schema::hasTable('servicios_generales_rutas_inspeccion_historial')) {
            Schema::create('servicios_generales_rutas_inspeccion_historial', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ruta_inspeccion_id');
                $table->foreign('ruta_inspeccion_id', 'sg_ruta_historial_ruta_fk')
                    ->references('id')
                    ->on('servicios_generales_rutas_inspeccion')
                    ->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('accion', 60);
                $table->string('estado_anterior', 30)->nullable();
                $table->string('estado_nuevo', 30)->nullable();
                $table->unsignedBigInteger('responsable_id')->nullable();
                $table->string('responsable_nombre', 200)->nullable();
                $table->text('observacion')->nullable();
                $table->json('cambios')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['ruta_inspeccion_id', 'created_at'], 'sg_ruta_historial_fecha_idx');
            });
        }

        if (!Schema::hasTable('coordinador_operador_agencia_historial')) {
            Schema::create('coordinador_operador_agencia_historial', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agencia_id')->nullable()->constrained('agencias')->nullOnDelete();
                $table->unsignedBigInteger('responsable_anterior_id')->nullable();
                $table->string('responsable_anterior_nombre', 200)->nullable();
                $table->unsignedBigInteger('responsable_nuevo_id')->nullable();
                $table->string('responsable_nuevo_nombre', 200)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('motivo');
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['agencia_id', 'created_at'], 'coa_historial_agencia_fecha_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coordinador_operador_agencia_historial');
        Schema::dropIfExists('servicios_generales_rutas_inspeccion_historial');

        if (Schema::hasTable('servicios_generales_rutas_inspeccion')) {
            Schema::table('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
                $columns = [
                    'agencia_id', 'coordinador_operador_id', 'responsable_nombre', 'tipo', 'prioridad',
                    'descripcion', 'detalle_solucion', 'evidencia_path', 'iniciado_at',
                    'cierre_solicitado_at', 'cerrado_at', 'cerrado_por',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('servicios_generales_rutas_inspeccion', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
