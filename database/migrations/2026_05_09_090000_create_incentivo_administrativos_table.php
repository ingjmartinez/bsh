<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incentivo_administrativos')) {
            return;
        }

        Schema::create('incentivo_administrativos', function (Blueprint $table) {
            $table->id();
            $table->string('grupo', 70);
            $table->string('nombre', 120);
            $table->string('empresa', 50);
            $table->decimal('pct_total', 8, 4)->default(0);
            $table->timestamps();

            $table->index(['grupo', 'empresa']);
            $table->unique(['grupo', 'nombre', 'empresa'], 'uniq_incentivo_adm_grupo_nombre_empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentivo_administrativos');
    }
};
