<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agencias')) {
            return;
        }

        Schema::create('agencias', function (Blueprint $table) {
            $table->id();
            $table->string('agencia', 25)->nullable();
            $table->string('terminal', 25)->nullable();
            $table->string('nombre_agencia', 55)->nullable();
            $table->string('sistema', 55)->nullable();
            $table->string('ciudad', 55)->nullable();
            $table->string('ruta', 55)->nullable();
            $table->string('operador', 55)->nullable();
            $table->string('coordinador', 55)->nullable();
            $table->boolean('aplica_incentivo')->default(false);
            $table->timestamps();

            $table->index('terminal');
            $table->index('agencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencias');
    }
};
