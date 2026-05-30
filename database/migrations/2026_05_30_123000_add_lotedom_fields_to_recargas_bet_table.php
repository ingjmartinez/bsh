<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recargas_bet')) {
            return;
        }

        Schema::table('recargas_bet', function (Blueprint $table) {
            if (!Schema::hasColumn('recargas_bet', 'consorcio_id')) {
                $table->unsignedBigInteger('consorcio_id')->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'consorcio_codigo')) {
                $table->string('consorcio_codigo', 25)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'consorcio_nombre')) {
                $table->string('consorcio_nombre', 100)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'banca_id')) {
                $table->unsignedBigInteger('banca_id')->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'banca_nombre')) {
                $table->string('banca_nombre', 150)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'producto_id')) {
                $table->bigInteger('producto_id')->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'producto_nombre')) {
                $table->string('producto_nombre', 100)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'terminal_codigo')) {
                $table->string('terminal_codigo', 25)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'terminal_nombre')) {
                $table->string('terminal_nombre', 150)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'descripcion')) {
                $table->string('descripcion', 100)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'distribuidora_id')) {
                $table->unsignedBigInteger('distribuidora_id')->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'distribuidora_nombre')) {
                $table->string('distribuidora_nombre', 100)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'proveedor_id')) {
                $table->unsignedBigInteger('proveedor_id')->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'proveedor_nombre')) {
                $table->string('proveedor_nombre', 100)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'comision')) {
                $table->decimal('comision', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('recargas_bet', 'comision_supervisor')) {
                $table->decimal('comision_supervisor', 18, 2)->nullable();
            }
        });

        Schema::table('recargas_bet', function (Blueprint $table) {
            if (!$this->hasIndex('recargas_bet', 'rb_fecha_terminal_idx')) {
                $table->index(['fecha', 'terminal_codigo'], 'rb_fecha_terminal_idx');
            }

            if (!$this->hasIndex('recargas_bet', 'rb_fecha_producto_idx')) {
                $table->index(['fecha', 'producto_id'], 'rb_fecha_producto_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('recargas_bet')) {
            return;
        }

        Schema::table('recargas_bet', function (Blueprint $table) {
            if ($this->hasIndex('recargas_bet', 'rb_fecha_terminal_idx')) {
                $table->dropIndex('rb_fecha_terminal_idx');
            }

            if ($this->hasIndex('recargas_bet', 'rb_fecha_producto_idx')) {
                $table->dropIndex('rb_fecha_producto_idx');
            }
        });

        Schema::table('recargas_bet', function (Blueprint $table) {
            foreach ([
                'consorcio_id',
                'consorcio_codigo',
                'consorcio_nombre',
                'banca_id',
                'banca_nombre',
                'producto_id',
                'producto_nombre',
                'terminal_codigo',
                'terminal_nombre',
                'descripcion',
                'distribuidora_id',
                'distribuidora_nombre',
                'proveedor_id',
                'proveedor_nombre',
                'comision',
                'comision_supervisor',
            ] as $column) {
                if (Schema::hasColumn('recargas_bet', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = DB::select('SHOW INDEX FROM `' . str_replace('`', '``', $table) . '` WHERE Key_name = ?', [$index]);

        return !empty($indexes);
    }
};
