<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consorcios')) {
            return;
        }

        Schema::create('consorcios', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('consorcios', 150)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consorcios');
    }
};
