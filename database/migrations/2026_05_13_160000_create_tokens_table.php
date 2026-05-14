<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tokens')) {
            return;
        }

        Schema::create('tokens', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->text('token');
            $table->timestamp('fecha')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
