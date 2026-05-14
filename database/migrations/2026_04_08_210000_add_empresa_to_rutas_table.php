<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutas', function (Blueprint $table) {
            if (!Schema::hasColumn('rutas', 'empresa')) {
                $table->string('empresa', 30)->nullable()->after('nombre_ruta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rutas', function (Blueprint $table) {
            if (Schema::hasColumn('rutas', 'empresa')) {
                $table->dropColumn('empresa');
            }
        });
    }
};
