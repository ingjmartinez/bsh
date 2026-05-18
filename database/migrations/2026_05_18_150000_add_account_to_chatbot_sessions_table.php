<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('chatbot_sessions', 'account')) {
                $table->string('account', 120)->default('default')->after('id');
            }
        });

        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->unique(['account', 'phone']);
            $table->index('account');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->dropUnique(['account', 'phone']);
            $table->dropIndex(['account']);
            $table->unique('phone');
        });

        Schema::table('chatbot_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('chatbot_sessions', 'account')) {
                $table->dropColumn('account');
            }
        });
    }
};
