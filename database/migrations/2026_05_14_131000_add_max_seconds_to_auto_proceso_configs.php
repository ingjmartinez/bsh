<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('auto_proceso_configs')) {
            return;
        }

        Schema::table('auto_proceso_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_proceso_configs', 'max_seconds')) {
                $table->unsignedSmallInteger('max_seconds')
                    ->default(1800)
                    ->after('correo')
                    ->comment('Tiempo maximo de ejecucion del auto proceso.');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('auto_proceso_configs')) {
            return;
        }

        Schema::table('auto_proceso_configs', function (Blueprint $table) {
            if (Schema::hasColumn('auto_proceso_configs', 'max_seconds')) {
                $table->dropColumn('max_seconds');
            }
        });
    }
};
