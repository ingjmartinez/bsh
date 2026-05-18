<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cuentas_contables')) {
            Schema::table('cuentas_contables', function (Blueprint $table) {
                if (! Schema::hasColumn('cuentas_contables', 'company_id')) {
                    $table->string('company_id', 20)->nullable()->after('id')->index();
                }
            });

            try {
                Schema::table('cuentas_contables', function (Blueprint $table) {
                    $table->dropUnique('cuentas_contables_cuenta_unique');
                });
            } catch (\Throwable) {
            }

            try {
                Schema::table('cuentas_contables', function (Blueprint $table) {
                    $table->unique(['company_id', 'cuenta'], 'cuentas_contables_company_cuenta_unique');
                });
            } catch (\Throwable) {
            }
        }

        if (Schema::hasTable('detalle_cuentas')) {
            Schema::table('detalle_cuentas', function (Blueprint $table) {
                if (! Schema::hasColumn('detalle_cuentas', 'company_id')) {
                    $table->string('company_id', 20)->nullable()->after('external_key')->index();
                }
            });

            try {
                Schema::table('detalle_cuentas', function (Blueprint $table) {
                    $table->index(['company_id', 'cuenta', 'fecha'], 'detalle_cuentas_company_cuenta_fecha_index');
                });
            } catch (\Throwable) {
            }
        }
    }

    public function down(): void
    {
        try {
            Schema::table('detalle_cuentas', function (Blueprint $table) {
                $table->dropIndex('detalle_cuentas_company_cuenta_fecha_index');
            });
        } catch (\Throwable) {
        }

        if (Schema::hasTable('detalle_cuentas')) {
            Schema::table('detalle_cuentas', function (Blueprint $table) {
                if (Schema::hasColumn('detalle_cuentas', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }

        try {
            Schema::table('cuentas_contables', function (Blueprint $table) {
                $table->dropUnique('cuentas_contables_company_cuenta_unique');
            });
        } catch (\Throwable) {
        }

        if (Schema::hasTable('cuentas_contables')) {
            Schema::table('cuentas_contables', function (Blueprint $table) {
                if (Schema::hasColumn('cuentas_contables', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }

        try {
            Schema::table('cuentas_contables', function (Blueprint $table) {
                $table->unique('cuenta', 'cuentas_contables_cuenta_unique');
            });
        } catch (\Throwable) {
        }
    }
};
