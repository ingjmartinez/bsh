<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios_generales_rutas_inspeccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre', 160)->nullable();
            $table->date('fecha')->nullable();
            $table->string('estado', 30)->default('borrador');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_generales_rutas_inspeccion');
    }
};
