<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            if (!Schema::hasColumn('reporte_diario_rutas', 'serial_ruta')) {
                $table->string('serial_ruta', 20)->nullable()->after('fecha');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            if (Schema::hasColumn('reporte_diario_rutas', 'serial_ruta')) {
                $table->dropColumn('serial_ruta');
            }
        });
    }
};
