<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $databaseName = config('database.connections.' . config('database.default') . '.database');
        $agenciaIdColumnType = '';

        if (!empty($databaseName)) {
            $row = DB::selectOne(
                'SELECT COLUMN_TYPE as column_type
                 FROM information_schema.columns
                 WHERE table_schema = ?
                   AND table_name = ?
                   AND column_name = ?
                 LIMIT 1',
                [$databaseName, 'agencias', 'id']
            );
            $agenciaIdColumnType = strtolower((string) ($row->column_type ?? ''));
        }

        $agenciaEsUnsigned = str_contains($agenciaIdColumnType, 'unsigned');

        Schema::create('ruta_agencia', function (Blueprint $table) use ($agenciaEsUnsigned) {
            $table->id();
            $table->unsignedBigInteger('ruta_id');
            $table->foreign('ruta_id')
                ->references('id')
                ->on('rutas')
                ->cascadeOnDelete();

            if ($agenciaEsUnsigned) {
                $table->unsignedInteger('agencia_id');
            } else {
                $table->integer('agencia_id');
            }

            $table->foreign('agencia_id')
                ->references('id')
                ->on('agencias')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ruta_id', 'agencia_id'], 'ruta_agencia_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_agencia');
    }
};
