<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tecnologia_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asignado_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20);
            $table->string('titulo', 160);
            $table->text('descripcion');
            $table->string('prioridad', 20)->default('media');
            $table->string('estado', 20)->default('pendiente');
            $table->text('detalle_solucion')->nullable();
            $table->timestamp('asignado_at')->nullable();
            $table->timestamp('resuelto_at')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index(['asignado_id', 'estado']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tecnologia_solicitudes');
    }
};
