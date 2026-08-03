<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('entradas_diario') || ! Schema::hasColumn('entradas_diario', 'id_viejo')) {
            return;
        }

        $indexExists = collect(Schema::getIndexes('entradas_diario'))
            ->contains(fn (array $index): bool => $index['name'] === 'entradas_diario_id_viejo_index');

        if ($indexExists) {
            return;
        }

        Schema::table('entradas_diario', function (Blueprint $table) {
            $table->index('id_viejo', 'entradas_diario_id_viejo_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('entradas_diario')) {
            return;
        }

        $indexExists = collect(Schema::getIndexes('entradas_diario'))
            ->contains(fn (array $index): bool => $index['name'] === 'entradas_diario_id_viejo_index');

        if (! $indexExists) {
            return;
        }

        Schema::table('entradas_diario', function (Blueprint $table) {
            $table->dropIndex('entradas_diario_id_viejo_index');
        });
    }
};
