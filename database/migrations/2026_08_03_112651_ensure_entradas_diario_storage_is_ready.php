<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('entradas_diario')) {
            Schema::create('entradas_diario', function (Blueprint $table): void {
                $table->id();
                $table->string('external_key', 80)->unique();
                $table->string('payload_hash', 64)->nullable();
                $table->string('no_asiento', 50)->nullable()->index();
                $table->string('company_id', 20)->nullable()->index();
                $table->date('fecha')->nullable()->index();
                $table->string('fecha_raw', 50)->nullable();
                $table->string('ref', 50)->nullable();
                $table->string('no_ref', 50)->nullable();
                $table->string('cuenta', 50)->nullable()->index();
                $table->decimal('debito', 18, 2)->default(0);
                $table->decimal('credito', 18, 2)->default(0);
                $table->text('descripcion')->nullable();
                $table->string('id_centro_costo', 50)->nullable()->index();
                $table->string('id_grupo', 50)->nullable();
                $table->string('id_sub_grupo', 50)->nullable();
                $table->string('id_division', 50)->nullable();
                $table->string('id_sociedad', 50)->nullable();
                $table->boolean('conciliado')->default(false);
                $table->string('modulo', 80)->nullable();
                $table->dateTime('fecha_grabado')->nullable();
                $table->dateTime('fecha_modificado')->nullable();
                $table->string('id_viejo', 80)->nullable()->index('entradas_diario_id_viejo_index');
                $table->string('centro_costo', 180)->nullable();
                $table->string('grupo', 180)->nullable();
                $table->string('sub_grupo', 180)->nullable();
                $table->string('division', 180)->nullable();
                $table->string('creado_por', 100)->nullable();
                $table->string('modificado_por', 100)->nullable();
                $table->string('ref_desc', 180)->nullable();
                $table->string('sociedad', 180)->nullable();
                $table->timestamps();

                $table->index(['company_id', 'fecha']);
                $table->index(['company_id', 'cuenta', 'fecha']);
                $table->index(['ref', 'no_ref']);
            });

            return;
        }

        if (! Schema::hasColumn('entradas_diario', 'id_viejo')) {
            Schema::table('entradas_diario', function (Blueprint $table): void {
                $table->string('id_viejo', 80)->nullable();
            });
        }

        $indexExists = collect(Schema::getIndexes('entradas_diario'))
            ->contains(fn (array $index): bool => $index['name'] === 'entradas_diario_id_viejo_index');

        if (! $indexExists) {
            Schema::table('entradas_diario', function (Blueprint $table): void {
                $table->index('id_viejo', 'entradas_diario_id_viejo_index');
            });
        }
    }

    public function down(): void
    {
        /**
         * This repair migration intentionally preserves accounting data on rollback.
         */
    }
};
