<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla principal de tareas/proyectos para el módulo Gantt.
     *
     * Estados: pendiente, en_progreso, completada, cancelada
     * Prioridades: baja, media, alta, critica
     */
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->foreignId('departamento_id')->constrained('departamentos')->cascadeOnDelete();
            $table->foreignId('user_id')->comment('Creador de la tarea')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asignado_id')->nullable()->comment('Usuario asignado')->constrained('users')->nullOnDelete();
            $table->foreignId('tarea_padre_id')->nullable()->comment('Subtarea de...')->constrained('tareas')->nullOnDelete();
            $table->enum('estado', ['pendiente', 'en_progreso', 'completada', 'cancelada'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->tinyInteger('progreso')->unsigned()->default(0)->comment('Porcentaje 0-100');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_completada')->nullable();
            $table->timestamps();

            $table->index(['departamento_id', 'estado']);
            $table->index(['asignado_id', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
