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
        Schema::create('monitoreo_venta_umbrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('users')->cascadeOnDelete();
            $table->string('categoria', 30);
            $table->decimal('monto', 18, 2);
            $table->timestamps();
            $table->unique(['asesor_id', 'categoria']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreo_venta_umbrals');
    }
};
