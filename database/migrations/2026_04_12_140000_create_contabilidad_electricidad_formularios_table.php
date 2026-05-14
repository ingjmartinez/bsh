<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('contabilidad_electricidad_seguimiento_dia')) {
            Schema::create('contabilidad_electricidad_seguimiento_dia', function (Blueprint $table) {
                $table->id();
                $table->date('fecha_solicitud');
                $table->string('distribuidora', 120);
                $table->string('nic', 80);
                $table->string('agencia', 150);
                $table->string('ruta', 150);
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['fecha_solicitud', 'distribuidora'], 'idx_seg_fecha_dist');
                $table->index('nic', 'idx_seg_nic');
            });
        }

        if (!Schema::hasTable('contabilidad_electricidad_averia_dia')) {
            Schema::create('contabilidad_electricidad_averia_dia', function (Blueprint $table) {
                $table->id();
                $table->date('fecha_reporte');
                $table->string('reporte', 120);
                $table->string('distribuidora', 120);
                $table->string('nic', 80);
                $table->string('agencia', 150);
                $table->string('ruta', 150);
                $table->string('coordinadores', 180)->nullable();
                $table->string('agente_venta_am', 180)->nullable();
                $table->string('agente_venta_pm', 180)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['fecha_reporte', 'distribuidora'], 'idx_ave_fecha_dist');
                $table->index('nic', 'idx_ave_nic');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contabilidad_electricidad_averia_dia');
        Schema::dropIfExists('contabilidad_electricidad_seguimiento_dia');
    }
};
