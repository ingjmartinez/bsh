<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coordinador_operador', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->unsignedBigInteger('cedula')->unique();
            $table->enum('puesto', ['coordinador', 'operador']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordinador_operador');
    }
};
