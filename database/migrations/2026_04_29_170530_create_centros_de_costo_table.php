<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('centros_de_costo')) {
            $this->ensureIndexes();

            return;
        }

        Schema::create('centros_de_costo', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_centro_costo')->unique();
            $table->string('company_id', 150)->nullable();
            $table->string('descripcion', 255);
            $table->string('cuenta', 100)->nullable();
            $table->boolean('inactivo')->default(false);
            $table->string('id_grupo', 150)->nullable();
            $table->string('id_sub_grupo', 150)->nullable();
            $table->string('id_division', 150)->nullable();
            $table->string('id_sociedad', 150)->nullable();
            $table->string('id_viejo', 150)->nullable();
            $table->string('id_centro_costo_resumir_en', 150)->nullable();
            $table->boolean('com_recarga')->default(false);
            $table->boolean('gasto_vta_tradicional')->default(false);
            $table->boolean('varios_locales')->default(false);
            $table->boolean('aplica_para_ponderar')->default(false);
            $table->decimal('valor_ponderar', 14, 4)->default(0);
            $table->string('creado_por', 100)->nullable();
            $table->dateTime('fecha_grabado')->nullable();
            $table->string('modificado_por', 100)->nullable();
            $table->dateTime('fecha_modificado')->nullable();
            $table->json('atributos')->nullable();
            $table->timestamps();

            $table->index('id_grupo');
        });

        $this->ensureIndexes();
    }

    public function down(): void
    {
        Schema::dropIfExists('centros_de_costo');
    }

    private function ensureIndexes(): void
    {
        Schema::table('centros_de_costo', function (Blueprint $table) {
            if (! $this->indexExists('centros_de_costo_id_centro_costo_unique')) {
                $table->unique('id_centro_costo');
            }

            if (! $this->indexExists('centros_de_costo_id_grupo_index')) {
                $table->index('id_grupo');
            }
        });

        if (! $this->indexExists('centros_de_costo_inactivo_descripcion_index')) {
            DB::statement(
                'ALTER TABLE centros_de_costo ADD INDEX centros_de_costo_inactivo_descripcion_index (inactivo, descripcion(191))'
            );
        }
    }

    private function indexExists(string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'centros_de_costo')
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
