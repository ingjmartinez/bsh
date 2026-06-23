<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agencias_lotedom')) {
            return;
        }

        Schema::create('agencias_lotedom', function (Blueprint $table) {
            $table->id();
            $table->string('agencia', 25)->nullable();
            $table->string('codigo', 25)->nullable();
            $table->string('nombre_agencia', 55)->nullable();
            $table->string('nombre', 55)->nullable();
            $table->string('terminal', 25)->nullable();
            $table->string('horario_am', 35)->nullable();
            $table->string('horario_pm', 35)->nullable();
            $table->string('sistema', 55)->nullable();
            $table->string('empresa', 60)->nullable();
            $table->string('ciudad', 55)->nullable();
            $table->string('ruta', 55)->nullable();
            $table->string('operador', 55)->nullable();
            $table->string('coordinador', 55)->nullable();
            $table->tinyInteger('estatus')->default(1);
            $table->boolean('aplica_incentivo')->default(true);
            $table->timestamps();

            $table->unique('terminal');
            $table->index('agencia');
            $table->index('codigo');
            $table->index('estatus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencias_lotedom');
    }
};
