<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('agencias', 'estatus')) {
            Schema::table('agencias', function (Blueprint $table) {
                $table->tinyInteger('estatus')->default(1)->after('coordinador');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('agencias', 'estatus')) {
            Schema::table('agencias', function (Blueprint $table) {
                $table->dropColumn('estatus');
            });
        }
    }
};
