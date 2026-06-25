<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empleados') || Schema::hasColumn('empleados', 'idcentrocosto')) {
            return;
        }

        Schema::table('empleados', function (Blueprint $table): void {
            $table->integer('idcentrocosto')->nullable()->after('empleadoid');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empleados') || !Schema::hasColumn('empleados', 'idcentrocosto')) {
            return;
        }

        Schema::table('empleados', function (Blueprint $table): void {
            $table->dropColumn('idcentrocosto');
        });
    }
};
