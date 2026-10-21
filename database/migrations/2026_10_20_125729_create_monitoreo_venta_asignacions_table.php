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
        Schema::create('monitoreo_venta_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->string('central', 100);
            $table->string('grupo', 100);
            $table->foreignId('asesor_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['central', 'grupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreo_venta_asignaciones');
    }
};
