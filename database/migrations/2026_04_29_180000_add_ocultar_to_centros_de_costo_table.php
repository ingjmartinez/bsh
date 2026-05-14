<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('centros_de_costo') || Schema::hasColumn('centros_de_costo', 'ocultar')) {
            return;
        }

        Schema::table('centros_de_costo', function (Blueprint $table) {
            $table->boolean('ocultar')->default(false)->after('id_viejo');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('centros_de_costo') || ! Schema::hasColumn('centros_de_costo', 'ocultar')) {
            return;
        }

        Schema::table('centros_de_costo', function (Blueprint $table) {
            $table->dropColumn('ocultar');
        });
    }
};
