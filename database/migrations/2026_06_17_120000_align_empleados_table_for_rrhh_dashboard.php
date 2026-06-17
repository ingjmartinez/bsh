<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empleados')) {
            return;
        }

        Schema::table('empleados', function (Blueprint $table): void {
            if (!Schema::hasColumn('empleados', 'tipo_documento_id')) {
                $table->unsignedBigInteger('tipo_documento_id')->nullable()->after('cedula');
            }

            if (!Schema::hasColumn('empleados', 'departamento_id')) {
                $table->unsignedBigInteger('departamento_id')->nullable()->after('apellidos');
            }

            if (!Schema::hasColumn('empleados', 'posicion_id')) {
                $table->unsignedBigInteger('posicion_id')->nullable()->after('departamento_id');
            }

            if (!Schema::hasColumn('empleados', 'ciudad_id')) {
                $table->unsignedBigInteger('ciudad_id')->nullable()->after('posicion_id');
            }

            if (!Schema::hasColumn('empleados', 'estado_civil_id')) {
                $table->unsignedBigInteger('estado_civil_id')->nullable()->after('ciudad_id');
            }

            if (!Schema::hasColumn('empleados', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('estado_civil_id');
            }

            if (!Schema::hasColumn('empleados', 'fecha_ingreso')) {
                $table->date('fecha_ingreso')->nullable()->after('fecha_nacimiento');
            }

            if (!Schema::hasColumn('empleados', 'fecha_egreso')) {
                $table->date('fecha_egreso')->nullable()->after('fecha_ingreso');
            }

            if (!Schema::hasColumn('empleados', 'turno_id')) {
                $table->unsignedBigInteger('turno_id')->nullable()->after('fecha_egreso');
            }

            if (!Schema::hasColumn('empleados', 'tipo_contrato')) {
                $table->string('tipo_contrato')->nullable()->after('turno_id');
            }

            if (!Schema::hasColumn('empleados', 'estatus')) {
                $table->unsignedTinyInteger('estatus')->default(1)->after('tipo_contrato');
            }

            if (!Schema::hasColumn('empleados', 'salario')) {
                $table->decimal('salario', 12, 2)->nullable()->after('estatus');
            }

            if (!Schema::hasColumn('empleados', 'banco_id')) {
                $table->unsignedBigInteger('banco_id')->nullable()->after('salario');
            }

            if (!Schema::hasColumn('empleados', 'numero_cuenta')) {
                $table->string('numero_cuenta', 50)->nullable()->after('banco_id');
            }

            if (!Schema::hasColumn('empleados', 'tipo_cuenta')) {
                $table->string('tipo_cuenta', 30)->nullable()->after('numero_cuenta');
            }

            if (!Schema::hasColumn('empleados', 'aplica_incentivo')) {
                $table->boolean('aplica_incentivo')->default(false)->after('tipo_cuenta');
            }

            if (!Schema::hasColumn('empleados', 'porcentaje_incentivo')) {
                $table->decimal('porcentaje_incentivo', 8, 2)->nullable()->after('aplica_incentivo');
            }

            if (!Schema::hasColumn('empleados', 'tipo_empleado_incentivo')) {
                $table->unsignedTinyInteger('tipo_empleado_incentivo')->nullable()->after('porcentaje_incentivo');
            }

            if (!Schema::hasColumn('empleados', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('tipo_empleado_incentivo');
            }

            if (!Schema::hasColumn('empleados', 'email')) {
                $table->string('email', 150)->nullable()->after('telefono');
            }

            if (!Schema::hasColumn('empleados', 'fuente_sync')) {
                $table->string('fuente_sync', 50)->nullable()->after('email');
            }

            if (!Schema::hasColumn('empleados', 'ultima_sync_at')) {
                $table->timestamp('ultima_sync_at')->nullable()->after('fuente_sync');
            }

            if (!Schema::hasColumn('empleados', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }
        });

        $this->copyLegacyData();
        $this->alignUniqueIndexes();
        $this->addMissingIndexes();
    }

    public function down(): void
    {
        // Intentionally left empty: this migration preserves production data and
        // only adds compatibility columns/indexes required by the current code.
    }

    private function copyLegacyData(): void
    {
        $copies = [
            ['salario', 'salariomensual'],
            ['numero_cuenta', 'ctabanco'],
            ['tipo_cuenta', 'tipocuenta'],
        ];

        foreach ($copies as [$target, $source]) {
            if (Schema::hasColumn('empleados', $target) && Schema::hasColumn('empleados', $source)) {
                DB::statement("UPDATE empleados SET {$target} = {$source} WHERE {$target} IS NULL AND {$source} IS NOT NULL");
            }
        }

        $dateCopies = [
            ['fecha_nacimiento', 'fechanacimiento'],
            ['fecha_ingreso', 'fechaingreso'],
            ['fecha_egreso', 'fechasalida'],
        ];

        foreach ($dateCopies as [$target, $source]) {
            if (Schema::hasColumn('empleados', $target) && Schema::hasColumn('empleados', $source)) {
                DB::statement("
                    UPDATE empleados
                    SET {$target} = CASE
                        WHEN {$source} IS NULL OR {$source} = '0000-00-00' THEN NULL
                        ELSE DATE({$source})
                    END
                    WHERE {$target} IS NULL
                ");
            }
        }

        if (Schema::hasColumn('empleados', 'telefono')) {
            if (Schema::hasColumn('empleados', 'tel1') && Schema::hasColumn('empleados', 'tel2')) {
                DB::statement("UPDATE empleados SET telefono = COALESCE(NULLIF(tel1, ''), NULLIF(tel2, '')) WHERE telefono IS NULL");
            } elseif (Schema::hasColumn('empleados', 'tel1')) {
                DB::statement("UPDATE empleados SET telefono = tel1 WHERE telefono IS NULL AND tel1 IS NOT NULL");
            }
        }

        if (Schema::hasColumn('empleados', 'estatus') && Schema::hasColumn('empleados', 'fecha_egreso')) {
            DB::statement('UPDATE empleados SET estatus = CASE WHEN fecha_egreso IS NULL THEN 1 ELSE 0 END');
        }

        if (Schema::hasColumn('empleados', 'fuente_sync')) {
            DB::statement("UPDATE empleados SET fuente_sync = 'apisj_rrhh' WHERE fuente_sync IS NULL");
        }
    }

    private function alignUniqueIndexes(): void
    {
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

            if ($index['unique'] && $index['columns'] === ['empleadoid']) {
                DB::statement("ALTER TABLE empleados DROP INDEX `{$name}`");
            }
        }

        $indexes = collect(DB::select('SHOW INDEX FROM empleados'))
            ->groupBy('Key_name')
            ->map(fn ($rows) => [
                'columns' => collect($rows)->sortBy('Seq_in_index')->pluck('Column_name')->values()->all(),
                'unique' => (int) collect($rows)->first()->Non_unique === 0,
            ]);

        $hasCompositeUnique = $indexes->contains(fn ($index) => $index['unique']
            && ($index['columns'] === ['companyid', 'empleadoid'] || $index['columns'] === ['empleadoid', 'companyid']));

        if (!$hasCompositeUnique) {
            Schema::table('empleados', function (Blueprint $table): void {
                $table->unique(['companyid', 'empleadoid'], 'empleados_empleadoid_companyid_unique');
            });
        }
    }

    private function addMissingIndexes(): void
    {
        $indexedColumns = collect(DB::select('SHOW INDEX FROM empleados'))->pluck('Column_name')->all();

        Schema::table('empleados', function (Blueprint $table) use ($indexedColumns): void {
            foreach (['departamento_id', 'posicion_id', 'ciudad_id', 'estatus', 'aplica_incentivo'] as $column) {
                if (Schema::hasColumn('empleados', $column) && !in_array($column, $indexedColumns, true)) {
                    $table->index($column);
                }
            }
        });
    }
};
