<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrevistas_online', function (Blueprint $table) {
            $table->id();

            $table->string('nombre_completo');
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('direccion', 500)->nullable();
            $table->string('estado_civil', 50)->nullable();
            $table->unsignedTinyInteger('hijos')->nullable();
            $table->string('estudia_actualmente', 255)->nullable();
            $table->string('licencia_vehiculo', 255)->nullable();
            $table->string('laborando_actualmente', 255)->nullable();
            $table->string('ultimo_empleo_posicion', 255)->nullable();
            $table->string('tiempo', 100)->nullable();
            $table->decimal('salario', 12, 2)->nullable();
            $table->string('fecha_salida_motivo', 500)->nullable();
            $table->text('comentarios')->nullable();
            $table->date('fecha_llamada')->nullable();
            $table->string('entrevistado_por', 150)->nullable();

            $table->string('vacante_aplica', 255)->nullable();
            $table->text('experiencia_demostrable')->nullable();
            $table->text('conoce_del_area')->nullable();
            $table->text('fortalezas')->nullable();
            $table->text('debilidades')->nullable();
            $table->unsignedTinyInteger('manejo_excel')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->index('fecha_llamada');
            $table->index('entrevistado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrevistas_online');
    }
};
