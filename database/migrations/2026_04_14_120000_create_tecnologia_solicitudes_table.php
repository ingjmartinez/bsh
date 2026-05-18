<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tecnologia_solicitudes')) {
            return;
        }

        Schema::create('tecnologia_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitante_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('asignado_a_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tipo_solicitud_id')->nullable()->constrained('tipos_solicitud_tecnologia')->nullOnDelete();
            $table->string('titulo', 160);
            $table->text('descripcion');
            $table->string('prioridad', 20)->default('media');
            $table->string('estado', 20)->default('pendiente');
            $table->unsignedTinyInteger('progreso')->default(0);
            $table->text('detalle_solucion')->nullable();
            $table->timestamp('cierre_solicitado_at')->nullable();
            $table->foreignId('cierre_solicitado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('asignado_at')->nullable();
            $table->date('fecha_completada')->nullable();
            $table->foreignId('cerrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('estado');
            $table->index(['asignado_a_id', 'estado']);
            $table->index(['solicitante_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tecnologia_solicitudes');
    }
};
