<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agencias') || !Schema::hasColumn('agencias', 'agencia')) {
            return;
        }

        Schema::table('agencias', function (Blueprint $table) {
            $table->string('agencia', 25)->nullable()->change();
        });

        DB::table('agencias')
            ->whereNotNull('terminal')
            ->whereRaw("TRIM(COALESCE(terminal, '')) <> ''")
            ->whereRaw("TRIM(COALESCE(agencia, '')) = TRIM(COALESCE(terminal, ''))")
            ->whereNull('nombre_agencia')
            ->whereNull('sistema')
            ->whereNull('ciudad')
            ->whereNull('ruta')
            ->whereNull('operador')
            ->whereNull('coordinador')
            ->update(['agencia' => null]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('agencias') || !Schema::hasColumn('agencias', 'agencia')) {
            return;
        }

        DB::table('agencias')
            ->whereNull('agencia')
            ->update(['agencia' => '']);

        Schema::table('agencias', function (Blueprint $table) {
            $table->string('agencia', 25)->nullable(false)->change();
        });
    }
};
