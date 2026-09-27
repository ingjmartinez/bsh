<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entradas_diario', function (Blueprint $table) {
            $table->index(['cuenta', 'fecha', 'id_centro_costo'], 'cxc_faltantes_cuenta_fecha_cc_idx');
            $table->index(['cuenta', 'id_division'], 'cxc_faltantes_cuenta_division_idx');
        });

        Schema::table('centros_de_costo', function (Blueprint $table) {
            $table->index('id_viejo', 'centros_de_costo_id_viejo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entradas_diario', function (Blueprint $table) {
            $table->dropIndex('cxc_faltantes_cuenta_fecha_cc_idx');
            $table->dropIndex('cxc_faltantes_cuenta_division_idx');
        });

        Schema::table('centros_de_costo', function (Blueprint $table) {
            $table->dropIndex('centros_de_costo_id_viejo_idx');
        });
    }
};
