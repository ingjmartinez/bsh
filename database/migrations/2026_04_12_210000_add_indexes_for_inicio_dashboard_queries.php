<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('VT_usuarios_Bet', 'idx_vt_bet_fecha', ['fecha']);
        $this->addIndexIfMissing('VT_usuarios_Bet', 'idx_vt_bet_fecha_producto', ['fecha', 'producto_id']);
        $this->addIndexIfMissing('VT_usuarios_Bet', 'idx_vt_bet_fecha_agencia', ['fecha', 'agencia_id']);

        $this->addIndexIfMissing('VT_usuarios_Net', 'idx_vt_net_fecha', ['fecha']);
        $this->addIndexIfMissing('VT_usuarios_Net', 'idx_vt_net_fecha_producto', ['fecha', 'producto_id']);
        $this->addIndexIfMissing('VT_usuarios_Net', 'idx_vt_net_fecha_agencia', ['fecha', 'agencia_id']);

        $this->addIndexIfMissing('catalogo_juegos', 'idx_catalogo_juegos_producto', ['producto_id']);
        $this->addIndexIfMissing('agencias', 'idx_agencias_estatus_terminal', ['estatus', 'terminal']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('VT_usuarios_Bet', 'idx_vt_bet_fecha');
        $this->dropIndexIfExists('VT_usuarios_Bet', 'idx_vt_bet_fecha_producto');
        $this->dropIndexIfExists('VT_usuarios_Bet', 'idx_vt_bet_fecha_agencia');

        $this->dropIndexIfExists('VT_usuarios_Net', 'idx_vt_net_fecha');
        $this->dropIndexIfExists('VT_usuarios_Net', 'idx_vt_net_fecha_producto');
        $this->dropIndexIfExists('VT_usuarios_Net', 'idx_vt_net_fecha_agencia');

        $this->dropIndexIfExists('catalogo_juegos', 'idx_catalogo_juegos_producto');
        $this->dropIndexIfExists('agencias', 'idx_agencias_estatus_terminal');
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        $columnsSql = implode(', ', array_map(static fn ($column) => "`{$column}`", $columns));
        try {
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columnsSql})");
        } catch (QueryException $e) {
            $errorCode = (string) ($e->errorInfo[1] ?? '');

            if ($errorCode !== '1061') {
                throw $e;
            }
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.statistics WHERE table_schema = ? AND LOWER(table_name) = LOWER(?) AND LOWER(index_name) = LOWER(?)',
            [$database, $table, $indexName]
        );

        return (int) ($row->total ?? 0) > 0;
    }
};
