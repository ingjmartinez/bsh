<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_proceso_configs', function (Blueprint $table) {
            $table->date('process_date')
                ->nullable()
                ->after('correo')
                ->comment('Fecha fija a procesar; si existe, tiene prioridad sobre offset');
        });
    }

    public function down(): void
    {
        Schema::table('auto_proceso_configs', function (Blueprint $table) {
            $table->dropColumn('process_date');
        });
    }
};
