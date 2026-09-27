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
        Schema::create('reporte_faltantes_alerta_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('minimo_faltantes')->default(3);
            $table->decimal('maximo_monto', 12, 2)->default(5000);
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_faltantes_alerta_configuraciones');
    }
};
