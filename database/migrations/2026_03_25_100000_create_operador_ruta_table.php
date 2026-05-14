<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operador_ruta', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->nullable();
            $table->unsignedBigInteger('cedula')->unique();
            $table->unsignedBigInteger('telefono')->nullable();
            $table->enum('puesto', ['coordinador', 'operador']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operador_ruta');
    }
};
