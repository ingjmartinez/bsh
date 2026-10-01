<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas_usuarios_bet', function (Blueprint $table) {
            $table->index(['tipo', 'fecha', 'monto'], 'vub_tipo_fecha_monto_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ventas_usuarios_bet', function (Blueprint $table) {
            $table->dropIndex('vub_tipo_fecha_monto_idx');
        });
    }
};
