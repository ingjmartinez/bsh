<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial/comentarios de las tareas.
     * Registra cada cambio de estado, comentario o actualización.
     */
    public function up(): void
    {
        Schema::create('tarea_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comentario');
            $table->enum('tipo', ['comentario', 'cambio_estado', 'cambio_progreso', 'reasignacion'])->default('comentario');
            $table->timestamps();

            $table->index('tarea_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarea_comentarios');
    }
};
