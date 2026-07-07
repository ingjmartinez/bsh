<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ventas_flash_bet')) {
            Schema::create('ventas_flash_bet', function (Blueprint $table) {
                $table->id();
                $table->string('grupo', 100)->nullable();
                $table->string('banca', 150)->nullable();
                $table->integer('numero_externo')->nullable();
                $table->decimal('venta_loteria', 18, 4)->nullable();
                $table->decimal('comision_loteria', 18, 6)->nullable();
                $table->decimal('premios_pagado', 18, 4)->nullable();
                $table->decimal('venta_recarga', 18, 4)->nullable();
                $table->decimal('comision_recarga', 18, 6)->nullable();
                $table->decimal('ventas_no_tradicional', 18, 4)->nullable();
                $table->decimal('premios_pagados_no_tradicional', 18, 4)->nullable();
                $table->decimal('comision_loterias_lot_no_tradicional', 18, 6)->nullable();
                $table->decimal('comision_gobierno', 18, 6)->nullable();
                $table->date('fecha')->nullable();

                $table->index('fecha', 'ventas_flash_bet_fecha_idx');
                $table->index(['fecha', 'numero_externo'], 'ventas_flash_bet_fecha_numero_idx');
            });

            return;
        }

        Schema::table('ventas_flash_bet', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_flash_bet', 'grupo')) {
                $table->string('grupo', 100)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'banca')) {
                $table->string('banca', 150)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'numero_externo')) {
                $table->integer('numero_externo')->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'venta_loteria')) {
                $table->decimal('venta_loteria', 18, 4)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'comision_loteria')) {
                $table->decimal('comision_loteria', 18, 6)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'premios_pagado')) {
                $table->decimal('premios_pagado', 18, 4)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'venta_recarga')) {
                $table->decimal('venta_recarga', 18, 4)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'comision_recarga')) {
                $table->decimal('comision_recarga', 18, 6)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'ventas_no_tradicional')) {
                $table->decimal('ventas_no_tradicional', 18, 4)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'premios_pagados_no_tradicional')) {
                $table->decimal('premios_pagados_no_tradicional', 18, 4)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'comision_loterias_lot_no_tradicional')) {
                $table->decimal('comision_loterias_lot_no_tradicional', 18, 6)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'comision_gobierno')) {
                $table->decimal('comision_gobierno', 18, 6)->nullable();
            }

            if (!Schema::hasColumn('ventas_flash_bet', 'fecha')) {
                $table->date('fecha')->nullable();
            }
        });

        Schema::table('ventas_flash_bet', function (Blueprint $table) {
            if (!$this->hasIndex('ventas_flash_bet', 'ventas_flash_bet_fecha_idx')) {
                $table->index('fecha', 'ventas_flash_bet_fecha_idx');
            }

            if (!$this->hasIndex('ventas_flash_bet', 'ventas_flash_bet_fecha_numero_idx')) {
                $table->index(['fecha', 'numero_externo'], 'ventas_flash_bet_fecha_numero_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ventas_flash_bet')) {
            return;
        }

        Schema::table('ventas_flash_bet', function (Blueprint $table) {
            if ($this->hasIndex('ventas_flash_bet', 'ventas_flash_bet_fecha_numero_idx')) {
                $table->dropIndex('ventas_flash_bet_fecha_numero_idx');
            }

            if ($this->hasIndex('ventas_flash_bet', 'ventas_flash_bet_fecha_idx')) {
                $table->dropIndex('ventas_flash_bet_fecha_idx');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $table = str_replace('`', '``', $table);

        return !empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]));
    }
};
