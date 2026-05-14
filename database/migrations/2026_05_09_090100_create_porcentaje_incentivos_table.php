<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('porcentaje_incentivos')) {
            return;
        }

        Schema::create('porcentaje_incentivos', function (Blueprint $table) {
            $table->id();
            $table->string('posicion', 80);
            $table->decimal('bono_pct', 8, 4)->default(0);
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            $table->unique('posicion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('porcentaje_incentivos');
    }
};
