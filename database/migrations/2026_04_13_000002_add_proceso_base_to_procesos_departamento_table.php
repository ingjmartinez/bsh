<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procesos_departamento', function (Blueprint $table) {
            $table->string('proceso_base', 150)->nullable()->after('departamento');
            $table->index(['departamento', 'proceso_base'], 'procesos_departamento_base_idx');
        });
    }

    public function down(): void
    {
        Schema::table('procesos_departamento', function (Blueprint $table) {
            $table->dropIndex('procesos_departamento_base_idx');
            $table->dropColumn('proceso_base');
        });
    }
};
