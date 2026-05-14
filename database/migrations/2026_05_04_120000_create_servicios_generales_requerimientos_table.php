<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios_generales_requerimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asignado_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 30);
            $table->string('titulo', 160);
            $table->text('descripcion');
            $table->string('prioridad', 20)->default('media');
            $table->string('estado', 20)->default('pendiente');
            $table->unsignedTinyInteger('progreso')->default(0);
            $table->text('detalle_solucion')->nullable();
            $table->timestamp('cierre_solicitado_at')->nullable();
            $table->foreignId('cierre_solicitado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('asignado_at')->nullable();
            $table->timestamp('resuelto_at')->nullable();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index(['asignado_id', 'estado']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_generales_requerimientos');
    }
};
