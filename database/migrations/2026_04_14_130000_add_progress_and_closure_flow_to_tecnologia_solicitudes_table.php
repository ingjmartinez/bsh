<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            $table->unsignedTinyInteger('progreso')->default(0)->after('estado');
            $table->timestamp('cierre_solicitado_at')->nullable()->after('detalle_solucion');
            $table->foreignId('cierre_solicitado_por')->nullable()->after('cierre_solicitado_at')->constrained('users')->nullOnDelete();
            $table->foreignId('cerrado_por')->nullable()->after('resuelto_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tecnologia_solicitudes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cerrado_por');
            $table->dropConstrainedForeignId('cierre_solicitado_por');
            $table->dropColumn(['cierre_solicitado_at', 'progreso']);
        });
    }
};
