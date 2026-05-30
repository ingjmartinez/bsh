<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ventas_producto_net')) {
            return;
        }

        Schema::table('ventas_producto_net', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_producto_net', 'consorcio_id')) {
                $table->unsignedBigInteger('consorcio_id')->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'consorcio_codigo')) {
                $table->string('consorcio_codigo', 25)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'consorcio_desc')) {
                $table->string('consorcio_desc', 100)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'banca_id')) {
                $table->unsignedBigInteger('banca_id')->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'banca_desc')) {
                $table->string('banca_desc', 150)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'terminal_codigo')) {
                $table->string('terminal_codigo', 25)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'terminal_desc')) {
                $table->string('terminal_desc', 150)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'loteria_id')) {
                $table->unsignedBigInteger('loteria_id')->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'loteria_desc')) {
                $table->string('loteria_desc', 100)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'descripcion')) {
                $table->string('descripcion', 100)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'monto_jugado')) {
                $table->decimal('monto_jugado', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'monto_pagado')) {
                $table->decimal('monto_pagado', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'monto_premiado')) {
                $table->decimal('monto_premiado', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'juego_id')) {
                $table->unsignedBigInteger('juego_id')->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'juego_prefijo')) {
                $table->string('juego_prefijo', 20)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'juego_desc')) {
                $table->string('juego_desc', 100)->nullable();
            }

            if (!Schema::hasColumn('ventas_producto_net', 'impuesto_retenido')) {
                $table->decimal('impuesto_retenido', 18, 2)->nullable();
            }
        });

        Schema::table('ventas_producto_net', function (Blueprint $table) {
            if (!$this->hasIndex('ventas_producto_net', 'vpn_fecha_terminal_idx')) {
                $table->index(['fecha', 'terminal_codigo'], 'vpn_fecha_terminal_idx');
            }

            if (!$this->hasIndex('ventas_producto_net', 'vpn_fecha_juego_idx')) {
                $table->index(['fecha', 'juego_id'], 'vpn_fecha_juego_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ventas_producto_net')) {
            return;
        }

        Schema::table('ventas_producto_net', function (Blueprint $table) {
            if ($this->hasIndex('ventas_producto_net', 'vpn_fecha_terminal_idx')) {
                $table->dropIndex('vpn_fecha_terminal_idx');
            }

            if ($this->hasIndex('ventas_producto_net', 'vpn_fecha_juego_idx')) {
                $table->dropIndex('vpn_fecha_juego_idx');
            }
        });

        Schema::table('ventas_producto_net', function (Blueprint $table) {
            foreach ([
                'consorcio_codigo',
                'consorcio_desc',
                'banca_id',
                'banca_desc',
                'terminal_codigo',
                'terminal_desc',
                'loteria_id',
                'loteria_desc',
                'monto_jugado',
                'monto_pagado',
                'monto_premiado',
                'juego_id',
                'juego_prefijo',
                'juego_desc',
                'impuesto_retenido',
            ] as $column) {
                if (Schema::hasColumn('ventas_producto_net', $column)) {
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
