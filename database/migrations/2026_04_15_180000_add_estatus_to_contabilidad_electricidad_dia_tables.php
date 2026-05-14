<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contabilidad_electricidad_seguimiento_dia', function (Blueprint $table) {
            $table->string('estatus', 30)->default('pendiente')->after('ruta');
        });

        Schema::table('contabilidad_electricidad_averia_dia', function (Blueprint $table) {
            $table->string('estatus', 30)->default('pendiente')->after('agente_venta_pm');
        });

        DB::table('contabilidad_electricidad_seguimiento_dia')
            ->whereNull('estatus')
            ->update(['estatus' => 'pendiente']);

        DB::table('contabilidad_electricidad_averia_dia')
            ->whereNull('estatus')
            ->update(['estatus' => 'pendiente']);
    }

    public function down(): void
    {
        Schema::table('contabilidad_electricidad_seguimiento_dia', function (Blueprint $table) {
            $table->dropColumn('estatus');
        });

        Schema::table('contabilidad_electricidad_averia_dia', function (Blueprint $table) {
            $table->dropColumn('estatus');
        });
    }
};
