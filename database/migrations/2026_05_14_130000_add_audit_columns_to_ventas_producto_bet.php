<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ventas_producto_bet')) {
            return;
        }

        Schema::table('ventas_producto_bet', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_producto_bet', 'consorcio_id')) {
                $table->unsignedBigInteger('consorcio_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('ventas_producto_bet', 'descripcion')) {
                $table->string('descripcion', 120)->nullable()->after('producto_id');
            }

            if (!Schema::hasColumn('ventas_producto_bet', 'fecha_sorteo')) {
                $table->dateTime('fecha_sorteo')->nullable()->after('sorteo_id');
            }

            if (!Schema::hasColumn('ventas_producto_bet', 'comision')) {
                $table->decimal('comision', 18, 2)->nullable()->after('monto');
            }

            if (!Schema::hasColumn('ventas_producto_bet', 'comision_supervisor')) {
                $table->decimal('comision_supervisor', 18, 2)->nullable()->after('comision');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ventas_producto_bet')) {
            return;
        }

        Schema::table('ventas_producto_bet', function (Blueprint $table) {
            foreach (['comision_supervisor', 'comision', 'fecha_sorteo', 'descripcion', 'consorcio_id'] as $column) {
                if (Schema::hasColumn('ventas_producto_bet', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
