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
        Schema::create('incentivo_periodo_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incentivo_periodo_id')
                ->constrained('incentivo_periodos')
                ->cascadeOnDelete();
            $table->string('cedula', 30);
            $table->string('empleadoid', 50)->nullable();
            $table->string('nombre', 200);
            $table->string('empresa', 100);
            $table->string('ultima_terminal', 50)->nullable();
            $table->string('ultima_agencia_nombre', 200)->nullable();
            $table->bigInteger('ventas_ultimo_mes')->default(0);
            $table->bigInteger('ventas_mes_actual')->default(0);
            $table->unsignedSmallInteger('dias_ventas')->default(0);
            $table->decimal('horas_total', 10, 2)->default(0);
            $table->bigInteger('incentivo_generado')->default(0);
            $table->bigInteger('monto_pagado')->default(0);
            $table->bigInteger('monto_no_pagado')->default(0);
            $table->string('estado', 30);
            $table->json('motivos')->nullable();
            $table->json('tipos_pago_detalle')->nullable();
            $table->timestamps();

            $table->unique(
                ['incentivo_periodo_id', 'cedula', 'empresa'],
                'incentivo_periodo_detalle_persona_unique'
            );
            $table->index(['incentivo_periodo_id', 'estado']);
            $table->index('cedula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentivo_periodo_detalles');
    }
};
