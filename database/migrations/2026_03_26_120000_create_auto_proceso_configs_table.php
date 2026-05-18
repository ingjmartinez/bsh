<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auto_proceso_configs')) {
            return;
        }

        Schema::create('auto_proceso_configs', function (Blueprint $table) {
            $table->id();
            $table->string('sistema', 20)->unique();
            $table->boolean('enabled')->default(false);
            $table->time('hora')->nullable();
            $table->string('correo')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_status', 20)->nullable();
            $table->json('last_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_proceso_configs');
    }
};
