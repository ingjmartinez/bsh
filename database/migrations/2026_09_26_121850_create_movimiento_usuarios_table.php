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
        Schema::create('movimiento_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->restrictOnDelete();
            $table->string('cedula', 30);
            $table->string('nombre_empleado', 200);
            $table->string('terminal_origen', 50)->nullable();
            $table->string('centro_costo_origen', 50)->nullable();
            $table->string('grupo_origen', 100)->nullable();
            $table->string('terminal_destino', 50)->nullable();
            $table->string('centro_costo_destino', 50)->nullable();
            $table->string('grupo_destino', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['cedula', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_usuarios');
    }
};
