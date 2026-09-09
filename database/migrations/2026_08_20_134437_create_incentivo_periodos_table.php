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
        Schema::create('incentivo_periodos', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('sistema', 20)->default('Todos');
            $table->string('modo_calculo', 30);
            $table->string('tipo_pago_defecto', 30);
            $table->unsignedSmallInteger('min_dias_venta')->default(1);
            $table->json('rangos_pago_por_tipo')->nullable();
            $table->json('terminales_excluidas')->nullable();
            $table->json('resumen')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['anio', 'mes'], 'incentivo_periodos_anio_mes_unique');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentivo_periodos');
    }
};
