<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 32)->index();
            $table->string('categoria', 40)->index();
            $table->string('ticket_numero', 80)->index();
            $table->string('estado', 30)->default('pendiente')->index();
            $table->text('mensaje_original')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('procesado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('procesado_at')->nullable()->index();
            $table->timestamps();

            $table->index(['categoria', 'estado']);
            $table->index(['created_at', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_solicitudes');
    }
};
