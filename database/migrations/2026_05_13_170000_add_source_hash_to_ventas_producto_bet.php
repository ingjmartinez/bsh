<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas_producto_bet', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_producto_bet', 'source_hash')) {
                $table->char('source_hash', 64)->nullable()->after('sorteo_id');
                $table->unique('source_hash', 'ventas_producto_bet_source_hash_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas_producto_bet', function (Blueprint $table) {
            if (Schema::hasColumn('ventas_producto_bet', 'source_hash')) {
                $table->dropUnique('ventas_producto_bet_source_hash_unique');
                $table->dropColumn('source_hash');
            }
        });
    }
};
