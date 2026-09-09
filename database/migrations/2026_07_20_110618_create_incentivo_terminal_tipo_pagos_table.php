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
        Schema::create('incentivo_terminal_tipo_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('sistema', 20);
            $table->string('terminal', 50);
            $table->date('fecha');
            $table->string('tipo_pago', 20);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['sistema', 'terminal', 'fecha'], 'incentivo_terminal_pago_dia_unique');
            $table->index(['fecha', 'tipo_pago'], 'incentivo_terminal_pago_fecha_tipo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentivo_terminal_tipo_pagos');
    }
};
