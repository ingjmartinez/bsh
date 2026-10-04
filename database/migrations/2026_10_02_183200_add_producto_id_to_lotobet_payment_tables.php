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
        foreach (['pagos_misma_empresa_bet', 'pagos_aotra_empresa_bet'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->integer('producto_id')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['pagos_misma_empresa_bet', 'pagos_aotra_empresa_bet'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropIndex(['producto_id']);
                $blueprint->dropColumn('producto_id');
            });
        }
    }
};
