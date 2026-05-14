<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            $table->longText('comprobante_entregado_path')->nullable()->after('observacion');
            $table->longText('comprobante_diferencia_path')->nullable()->after('comprobante_entregado_path');
            $table->timestamp('enviado_operador_at')->nullable()->after('comprobante_diferencia_path');
        });
    }

    public function down(): void
    {
        Schema::table('reporte_diario_rutas', function (Blueprint $table) {
            $table->dropColumn([
                'comprobante_entregado_path',
                'comprobante_diferencia_path',
                'enviado_operador_at',
            ]);
        });
    }
};
