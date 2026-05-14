<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('novedades_horario')) {
            return;
        }

        Schema::create('novedades_horario', function (Blueprint $table) {
            $table->id();
            $table->string('terminal', 50)->nullable();
            $table->string('nombre_agencia', 180)->nullable();
            $table->string('ruta', 120)->nullable();
            $table->string('nombre_empleado', 180)->nullable();
            $table->string('cedula', 25)->nullable();
            $table->date('fecha')->nullable();
            $table->dateTime('primer_login')->nullable();
            $table->dateTime('ultimo_login')->nullable();
            $table->decimal('horas_acumuladas', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['fecha', 'terminal']);
            $table->index('ruta');
            $table->index('cedula');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedades_horario');
    }
};
