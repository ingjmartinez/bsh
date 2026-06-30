<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'coordinador_operador',
        'coordinadores_operador',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'cargo')) {
                    $column = $blueprint->string('cargo', 50)->nullable();

                    if (Schema::hasColumn($table, 'puesto')) {
                        $column->after('puesto');
                    } elseif (Schema::hasColumn($table, 'apellido')) {
                        $column->after('apellido');
                    }
                }
            });

            if (Schema::hasColumn($table, 'cedula')) {
                DB::statement("ALTER TABLE {$table} MODIFY cedula VARCHAR(11) NULL");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'cedula')) {
                DB::statement("UPDATE {$table} SET cedula = LPAD(CAST(id AS CHAR), 11, '0') WHERE cedula IS NULL");
                DB::statement("ALTER TABLE {$table} MODIFY cedula VARCHAR(11) NOT NULL");
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'cargo')) {
                    $blueprint->dropColumn('cargo');
                }
            });
        }
    }
};
