<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('novedades_horario')) {
            return;
        }

        if (! Schema::hasColumn('novedades_horario', 'primer_login')) {
            Schema::table('novedades_horario', function (Blueprint $table) {
                $table->dateTime('primer_login')->nullable()->after('fecha');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('novedades_horario') || ! Schema::hasColumn('novedades_horario', 'primer_login')) {
            return;
        }

        Schema::table('novedades_horario', function (Blueprint $table) {
            $table->dropColumn('primer_login');
        });
    }
};
