<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ventas_producto_bet')) {
            return;
        }

        Schema::create('ventas_producto_bet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consorcio_id')->nullable();
            $table->string('agencia_id', 25);
            $table->unsignedBigInteger('producto_id');
            $table->string('descripcion', 120)->nullable();
            $table->decimal('monto', 18, 2)->default(0);
            $table->decimal('comision', 18, 2)->nullable();
            $table->decimal('comision_supervisor', 18, 2)->nullable();
            $table->date('fecha');
            $table->unsignedBigInteger('sorteo_id')->nullable();
            $table->dateTime('fecha_sorteo')->nullable();
            $table->char('source_hash', 64)->nullable();
            $table->timestamps();

            $table->unique('source_hash', 'ventas_producto_bet_source_hash_unique');
            $table->index(['fecha', 'agencia_id'], 'ventas_producto_bet_fecha_agencia_idx');
            $table->index(['fecha', 'producto_id'], 'ventas_producto_bet_fecha_producto_idx');
            $table->index('agencia_id');
            $table->index('producto_id');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_producto_bet');
    }
};
