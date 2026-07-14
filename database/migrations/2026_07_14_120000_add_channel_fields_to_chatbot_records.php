<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_sessions', function (Blueprint $table): void {
            $table->string('channel', 20)->default('whatsapp')->after('phone')->index();
            $table->string('channel_recipient', 120)->nullable()->after('channel')->index();
            $table->unique(['channel', 'channel_recipient'], 'chatbot_channel_recipient_unique');
        });

        Schema::table('ticket_solicitudes', function (Blueprint $table): void {
            $table->string('source_channel', 20)->default('whatsapp')->after('phone')->index();
            $table->string('source_recipient', 120)->nullable()->after('source_channel')->index();
        });

        Schema::table('servicios_generales_requerimientos', function (Blueprint $table): void {
            $table->string('source_channel', 20)->default('whatsapp')->after('whatsapp_phone')->index();
            $table->string('source_recipient', 120)->nullable()->after('source_channel')->index();
        });
    }

    public function down(): void
    {
        Schema::table('servicios_generales_requerimientos', function (Blueprint $table): void {
            $table->dropIndex(['source_recipient']);
            $table->dropIndex(['source_channel']);
            $table->dropColumn(['source_channel', 'source_recipient']);
        });

        Schema::table('ticket_solicitudes', function (Blueprint $table): void {
            $table->dropIndex(['source_recipient']);
            $table->dropIndex(['source_channel']);
            $table->dropColumn(['source_channel', 'source_recipient']);
        });

        Schema::table('chatbot_sessions', function (Blueprint $table): void {
            $table->dropUnique('chatbot_channel_recipient_unique');
            $table->dropIndex(['channel_recipient']);
            $table->dropIndex(['channel']);
            $table->dropColumn(['channel', 'channel_recipient']);
        });
    }
};
