<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empleados') || !Schema::hasColumn('empleados', 'cedula')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM empleados'))
            ->groupBy('Key_name')
            ->map(fn ($rows) => [
                'columns' => collect($rows)->sortBy('Seq_in_index')->pluck('Column_name')->values()->all(),
                'unique' => (int) collect($rows)->first()->Non_unique === 0,
            ]);

        foreach ($indexes as $name => $index) {
            if ($name === 'PRIMARY') {
                continue;
            }

            if ($index['unique'] && $index['columns'] === ['cedula']) {
                Schema::table('empleados', function (Blueprint $table) use ($name): void {
                    $table->dropUnique($name);
                });
            }
        }

        $indexes = collect(DB::select('SHOW INDEX FROM empleados'))
            ->groupBy('Key_name')
            ->map(fn ($rows) => collect($rows)->sortBy('Seq_in_index')->pluck('Column_name')->values()->all());

        $hasCedulaIndex = $indexes->contains(fn ($columns) => $columns === ['cedula']);

        if (!$hasCedulaIndex) {
            Schema::table('empleados', function (Blueprint $table): void {
                $table->index('cedula', 'empleados_cedula_index');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('empleados') || !Schema::hasColumn('empleados', 'cedula')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM empleados'))
            ->groupBy('Key_name')
            ->map(fn ($rows) => collect($rows)->sortBy('Seq_in_index')->pluck('Column_name')->values()->all());

        if ($indexes->has('empleados_cedula_index')) {
            Schema::table('empleados', function (Blueprint $table): void {
                $table->dropIndex('empleados_cedula_index');
            });
        }

        $hasCedulaUnique = collect(DB::select('SHOW INDEX FROM empleados'))
            ->groupBy('Key_name')
            ->contains(function ($rows) {
                $columns = collect($rows)->sortBy('Seq_in_index')->pluck('Column_name')->values()->all();
                $unique = (int) collect($rows)->first()->Non_unique === 0;

                return $unique && $columns === ['cedula'];
            });

        if (!$hasCedulaUnique) {
            Schema::table('empleados', function (Blueprint $table): void {
                $table->unique('cedula', 'empleados_cedula_unique');
            });
        }
    }
};
