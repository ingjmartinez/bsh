<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `ventas_usuarios_bet` MODIFY `producto_id` BIGINT NULL');
    }

    public function down(): void
    {
        DB::table('ventas_usuarios_bet')
            ->where('producto_id', '<', 0)
            ->update(['producto_id' => null]);

        DB::statement('ALTER TABLE `ventas_usuarios_bet` MODIFY `producto_id` BIGINT UNSIGNED NULL');
    }
};
