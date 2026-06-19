<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ticket_solicitudes')) {
            return;
        }

        Schema::table('ticket_solicitudes', function (Blueprint $table): void {
            if (!Schema::hasColumn('ticket_solicitudes', 'tomado_por_id')) {
                $table->foreignId('tomado_por_id')
                    ->nullable()
                    ->after('notas')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('ticket_solicitudes', 'tomado_at')) {
                $table->timestamp('tomado_at')
                    ->nullable()
                    ->after('tomado_por_id')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ticket_solicitudes')) {
            return;
        }

        Schema::table('ticket_solicitudes', function (Blueprint $table): void {
            if (Schema::hasColumn('ticket_solicitudes', 'tomado_por_id')) {
                $table->dropConstrainedForeignId('tomado_por_id');
            }

            if (Schema::hasColumn('ticket_solicitudes', 'tomado_at')) {
                $table->dropColumn('tomado_at');
            }
        });
    }
};
