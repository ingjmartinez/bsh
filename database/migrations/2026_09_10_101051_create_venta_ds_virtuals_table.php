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
        Schema::create('ventas_ds_virtual', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consorcio_id');
            $table->date('fecha');
            $table->string('agencia_id', 20);
            $table->decimal('ventas', 18, 2)->default(0);
            $table->decimal('premios_pagados', 18, 2)->default(0);
            $table->unsignedBigInteger('proveedor_id');
            $table->decimal('premios', 18, 2)->default(0);
            $table->string('proveedor_nombre', 100);
            $table->timestamps();

            $table->unique(
                ['fecha', 'consorcio_id', 'agencia_id', 'proveedor_id'],
                'ventas_ds_virtual_registro_unique'
            );
            $table->index(['fecha', 'agencia_id'], 'ventas_ds_virtual_fecha_agencia_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_ds_virtual');
    }
};
