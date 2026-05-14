<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_diario_rutas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('ruta_id');
            $table->unsignedBigInteger('operador_ruta_id');
            $table->decimal('entregado', 12, 2)->default(0);
            $table->decimal('procesado', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->string('correo_destino', 150);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('ruta_id')
                ->references('id')
                ->on('rutas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('operador_ruta_id')
                ->references('id')
                ->on('operador_ruta')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['fecha', 'ruta_id'], 'idx_reporte_diario_rutas_fecha_ruta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_diario_rutas');
    }
};
