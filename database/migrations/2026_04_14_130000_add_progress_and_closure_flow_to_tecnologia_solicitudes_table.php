<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tecnologia_solicitudes')) {
            return;
        }

        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('tecnologia_solicitudes', 'progreso')) {
                $table->unsignedTinyInteger('progreso')->default(0);
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'cierre_solicitado_at')) {
                $table->timestamp('cierre_solicitado_at')->nullable();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'cierre_solicitado_por_id')) {
                $table->foreignId('cierre_solicitado_por_id')->nullable()->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'fecha_completada')) {
                $table->date('fecha_completada')->nullable();
            }

            if (!Schema::hasColumn('tecnologia_solicitudes', 'cerrado_por_id')) {
                $table->foreignId('cerrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tecnologia_solicitudes')) {
            return;
        }

        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('tecnologia_solicitudes', 'cerrado_por_id')) {
                $table->dropConstrainedForeignId('cerrado_por_id');
            }

            if (Schema::hasColumn('tecnologia_solicitudes', 'cierre_solicitado_por_id')) {
                $table->dropConstrainedForeignId('cierre_solicitado_por_id');
            }

            foreach (['fecha_completada', 'cierre_solicitado_at', 'progreso'] as $column) {
                if (Schema::hasColumn('tecnologia_solicitudes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
