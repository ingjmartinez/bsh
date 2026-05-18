<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 32)->unique();
            $table->string('step', 80)->default('inicio')->index();
            $table->json('context')->nullable();
            $table->text('last_message')->nullable();
            $table->timestamp('last_interaction_at')->nullable()->index();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
    }
};
