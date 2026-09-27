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
        Schema::create('agencia_sin_actividad_motivos', function (Blueprint $table) {
            $table->id();
            $table->string('sistema', 25);
            $table->string('terminal', 50);
            $table->string('motivo', 100);
            $table->text('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['sistema', 'terminal']);
            $table->index(['sistema', 'motivo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencia_sin_actividad_motivos');
    }
};
