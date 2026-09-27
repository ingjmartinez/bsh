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
        foreach (['agencias', 'agencias_lotedom'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'grupo')) {
                    $table->string('grupo', 75)->nullable();
                }

                if (! Schema::hasColumn($tableName, 'central')) {
                    $table->string('central', 75)->nullable();
                }

                if (! Schema::hasColumn($tableName, 'gerente_de_servicio')) {
                    $table->string('gerente_de_servicio', 75)->nullable();
                }

                if (! Schema::hasColumn($tableName, 'tipo_pago')) {
                    $table->string('tipo_pago', 75)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['agencias', 'agencias_lotedom'] as $tableName) {
            $columns = collect(['grupo', 'central', 'gerente_de_servicio', 'tipo_pago'])
                ->filter(fn (string $column): bool => Schema::hasColumn($tableName, $column))
                ->all();

            if ($columns !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
