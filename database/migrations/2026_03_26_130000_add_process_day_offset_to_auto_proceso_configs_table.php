<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_proceso_configs', function (Blueprint $table) {
            $table->tinyInteger('process_day_offset')
                ->default(0)
                ->after('correo')
                ->comment('0: mismo dia, -1: dia de ayer');
        });
    }

    public function down(): void
    {
        Schema::table('auto_proceso_configs', function (Blueprint $table) {
            $table->dropColumn('process_day_offset');
        });
    }
};
