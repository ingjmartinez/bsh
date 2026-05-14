<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coordinador_operador_agencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coordinador_operador_id');
            $table->foreign('coordinador_operador_id')
                ->references('id')
                ->on('coordinador_operador')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('agencia_id');
            $table->foreign('agencia_id')
                ->references('id')
                ->on('agencias')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coordinador_operador_id', 'agencia_id'], 'coor_oper_agencia_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordinador_operador_agencia');
    }
};
