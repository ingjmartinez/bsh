<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('faltantes_delta')) {
            return;
        }

        Schema::create('faltantes_delta', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->bigInteger('id_trans')->nullable();
            $table->integer('id_tipo_trans')->nullable();
            $table->text('concepto')->nullable();
            $table->integer('estatus')->nullable();
            $table->dateTime('fec_transaccion')->nullable();
            $table->dateTime('fec_inclusion')->nullable();
            $table->string('usr_inclusion', 50)->nullable();
            $table->string('id_cuenta', 50)->nullable();
            $table->decimal('debito', 18, 2)->nullable();
            $table->decimal('credito', 18, 2)->nullable();
            $table->string('numero', 50)->nullable();
            $table->string('nombre_cuenta', 255)->nullable();

            $table->index(['fecha', 'numero'], 'faltantes_delta_fecha_numero_idx');
            $table->index(['fecha', 'id_trans'], 'faltantes_delta_fecha_trans_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faltantes_delta');
    }
};
