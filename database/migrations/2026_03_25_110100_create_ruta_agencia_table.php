<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ruta_agencia')) {
            Schema::create('ruta_agencia', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ruta_id');
                $table->unsignedBigInteger('agencia_id');
                $table->timestamps();

                $table->foreign('ruta_id')
                    ->references('id')
                    ->on('rutas')
                    ->cascadeOnDelete();

                $table->foreign('agencia_id')
                    ->references('id')
                    ->on('agencias')
                    ->cascadeOnDelete();

                $table->unique(['ruta_id', 'agencia_id'], 'ruta_agencia_unique');
            });

            return;
        }

        DB::statement('ALTER TABLE `ruta_agencia` MODIFY `agencia_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('ruta_agencia', function (Blueprint $table) {
            if (!$this->foreignKeyExists('ruta_agencia', 'ruta_agencia_ruta_id_foreign')) {
                $table->foreign('ruta_id')
                    ->references('id')
                    ->on('rutas')
                    ->cascadeOnDelete();
            }

            if (!$this->foreignKeyExists('ruta_agencia', 'ruta_agencia_agencia_id_foreign')) {
                $table->foreign('agencia_id')
                    ->references('id')
                    ->on('agencias')
                    ->cascadeOnDelete();
            }

            if (!$this->indexExists('ruta_agencia', 'ruta_agencia_unique')) {
                $table->unique(['ruta_id', 'agencia_id'], 'ruta_agencia_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_agencia');
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
