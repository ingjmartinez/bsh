<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            $table->decimal('gasto', 12, 2)->default(0)->after('procesado');
        });
    }

    public function down(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            $table->dropColumn('gasto');
        });
    }
};
