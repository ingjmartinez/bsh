<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agencias')) {
            return;
        }

        Schema::table('agencias', function (Blueprint $table) {
            if (!Schema::hasColumn('agencias', 'empresa')) {
                $table->string('empresa', 60)->nullable()->after('sistema');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('agencias')) {
            return;
        }

        Schema::table('agencias', function (Blueprint $table) {
            if (Schema::hasColumn('agencias', 'empresa')) {
                $table->dropColumn('empresa');
            }
        });
    }
};
