<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('operador_ruta_agencia')) {
            Schema::create('operador_ruta_agencia', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('operador_ruta_id');
                $table->unsignedBigInteger('agencia_id');
                $table->timestamps();

                $table->foreign('operador_ruta_id')
                    ->references('id')
                    ->on('operador_ruta')
                    ->cascadeOnDelete();

                $table->foreign('agencia_id')
                    ->references('id')
                    ->on('agencias')
                    ->cascadeOnDelete();

                $table->unique(['operador_ruta_id', 'agencia_id'], 'operador_ruta_agencia_unique');
            });

            return;
        }

        DB::statement('ALTER TABLE `operador_ruta_agencia` MODIFY `agencia_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('operador_ruta_agencia', function (Blueprint $table) {
            if (!$this->foreignKeyExists('operador_ruta_agencia', 'operador_ruta_agencia_operador_ruta_id_foreign')) {
                $table->foreign('operador_ruta_id')
                    ->references('id')
                    ->on('operador_ruta')
                    ->cascadeOnDelete();
            }

            if (!$this->foreignKeyExists('operador_ruta_agencia', 'operador_ruta_agencia_agencia_id_foreign')) {
                $table->foreign('agencia_id')
                    ->references('id')
                    ->on('agencias')
                    ->cascadeOnDelete();
            }

            if (!$this->indexExists('operador_ruta_agencia', 'operador_ruta_agencia_unique')) {
                $table->unique(['operador_ruta_id', 'agencia_id'], 'operador_ruta_agencia_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operador_ruta_agencia');
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS total
             FROM information_schema.referential_constraints
             WHERE constraint_schema = ?
               AND table_name = ?
               AND constraint_name = ?',
            [DB::getDatabaseName(), $table, $constraint]
        );

        return (int) ($row->total ?? 0) > 0;
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS total
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?',
            [DB::getDatabaseName(), $table, $index]
        );

        return (int) ($row->total ?? 0) > 0;
    }
};
