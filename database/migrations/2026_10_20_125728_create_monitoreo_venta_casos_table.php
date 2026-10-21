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
        Schema::create('monitoreo_venta_casos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained('agencias')->cascadeOnDelete();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha');
            $table->string('central', 100)->nullable();
            $table->string('grupo', 100)->nullable();
            $table->string('ruta')->nullable();
            $table->decimal('tradicional', 18, 2)->default(0);
            $table->decimal('no_tradicional', 18, 2)->default(0);
            $table->decimal('recargas', 18, 2)->default(0);
            $table->string('estado', 20)->default('pendiente');
            $table->text('causa')->nullable();
            $table->text('gestion')->nullable();
            $table->text('resultado')->nullable();
            $table->timestamp('cerrado_at')->nullable();
            $table->timestamps();
            $table->unique(['agencia_id', 'fecha']);
            $table->index(['asesor_id', 'estado']);
            $table->index(['central', 'grupo', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreo_venta_casos');
    }
};
