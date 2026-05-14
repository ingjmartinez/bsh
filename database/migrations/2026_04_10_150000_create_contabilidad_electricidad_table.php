<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contabilidad_electricidad', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_factura');
            $table->string('empresa', 80);
            $table->string('sucursal', 120);
            $table->string('contrato', 50)->nullable();
            $table->string('medidor', 50)->nullable();
            $table->decimal('lectura_anterior', 14, 3)->default(0);
            $table->decimal('lectura_actual', 14, 3)->default(0);
            $table->decimal('ajuste_kwh', 14, 3)->default(0);
            $table->decimal('tarifa_kwh', 14, 4)->default(0);
            $table->decimal('otros_cargos', 14, 2)->default(0);
            $table->decimal('impuestos', 14, 2)->default(0);
            $table->boolean('pagado')->default(false);
            $table->date('fecha_pago')->nullable();
            $table->string('referencia_pago', 120)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['fecha_factura', 'empresa']);
            $table->index(['empresa', 'sucursal']);
            $table->index('pagado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contabilidad_electricidad');
    }
};
