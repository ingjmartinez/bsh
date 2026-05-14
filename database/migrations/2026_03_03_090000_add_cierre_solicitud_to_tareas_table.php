<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->timestamp('cierre_solicitado_at')->nullable()->after('fecha_completada');
            $table->unsignedBigInteger('cierre_solicitado_por')->nullable()->after('cierre_solicitado_at');

            $table->foreign('cierre_solicitado_por')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropForeign(['cierre_solicitado_por']);
            $table->dropColumn(['cierre_solicitado_at', 'cierre_solicitado_por']);
        });
    }
};
