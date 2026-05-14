<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procesos_departamento', function (Blueprint $table) {
            $table->id();
            $table->string('departamento', 50);
            $table->string('nombre', 150);
            $table->string('icono', 80)->default('ri-file-list-3-line');
            $table->string('descripcion', 500)->nullable();
            $table->longText('protocolo')->nullable();
            $table->boolean('es_personalizado')->default(true);
            $table->timestamps();

            $table->index('departamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesos_departamento');
    }
};
