<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('servicios_generales_rutas_inspeccion')) {
            return;
        }

        Schema::table('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
            $table->index('agencia_id', 'sg_ruta_agencia_idx');
            $table->index(['coordinador_operador_id', 'estado'], 'sg_ruta_responsable_estado_idx');
            $table->index(['tipo', 'estado', 'fecha'], 'sg_ruta_tipo_estado_fecha_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('servicios_generales_rutas_inspeccion')) {
            return;
        }

        Schema::table('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
            $table->dropIndex('sg_ruta_agencia_idx');
            $table->dropIndex('sg_ruta_responsable_estado_idx');
            $table->dropIndex('sg_ruta_tipo_estado_fecha_idx');
        });
    }
};
