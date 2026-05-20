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

        Schema::table('ticket_solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_solicitudes', 'attachment_url')) {
                $table->text('attachment_url')->nullable()->after('mensaje_original');
            }

            if (!Schema::hasColumn('ticket_solicitudes', 'attachment_message_id')) {
                $table->string('attachment_message_id', 120)->nullable()->after('attachment_url')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ticket_solicitudes')) {
            return;
        }

        Schema::table('ticket_solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_solicitudes', 'attachment_message_id')) {
                $table->dropIndex(['attachment_message_id']);
                $table->dropColumn('attachment_message_id');
            }

            if (Schema::hasColumn('ticket_solicitudes', 'attachment_url')) {
                $table->dropColumn('attachment_url');
            }
        });
    }
};
