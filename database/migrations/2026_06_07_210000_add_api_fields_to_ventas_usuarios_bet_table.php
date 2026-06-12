<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas_usuarios_bet', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_usuarios_bet', 'consorcio_id')) {
                $table->unsignedBigInteger('consorcio_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('ventas_usuarios_bet', 'producto_id')) {
                $table->bigInteger('producto_id')->nullable()->after('agencia_id');
            }

            if (!Schema::hasColumn('ventas_usuarios_bet', 'descripcion')) {
                $table->string('descripcion', 255)->nullable()->after('producto_id');
            }

            if (!Schema::hasColumn('ventas_usuarios_bet', 'tipo')) {
                $table->string('tipo', 50)->nullable()->after('descripcion');
            }
        });

        $this->addIndexIfMissing('ventas_usuarios_bet', 'vub_fecha_producto_idx', ['fecha', 'producto_id']);
        $this->addIndexIfMissing('ventas_usuarios_bet', 'vub_producto_id_idx', ['producto_id']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('ventas_usuarios_bet', 'vub_fecha_producto_idx');
        $this->dropIndexIfExists('ventas_usuarios_bet', 'vub_producto_id_idx');

        Schema::table('ventas_usuarios_bet', function (Blueprint $table) {
            foreach (['tipo', 'descripcion', 'producto_id', 'consorcio_id'] as $column) {
                if (Schema::hasColumn('ventas_usuarios_bet', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if (!$this->indexExists($table, $indexName)) {
            $columnsSql = implode(', ', array_map(static fn ($column) => "`{$column}`", $columns));

            try {
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columnsSql})");
            } catch (QueryException $e) {
                if ((string) ($e->errorInfo[1] ?? '') !== '1061') {
                    throw $e;
                }
            }
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [DB::getDatabaseName(), $table, $indexName]
        );

        return (int) ($row->total ?? 0) > 0;
    }
};
