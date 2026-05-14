<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            if (!Schema::hasColumn('reporte_diario_rutas', 'banco_nombre')) {
                $table->string('banco_nombre', 150)->nullable()->after('operador_ruta_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            if (Schema::hasColumn('reporte_diario_rutas', 'banco_nombre')) {
                $table->dropColumn('banco_nombre');
            }
        });
    }
};

