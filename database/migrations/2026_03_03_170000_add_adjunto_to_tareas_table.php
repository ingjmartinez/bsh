<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->string('adjunto_path')->nullable()->after('descripcion');
            $table->string('adjunto_nombre')->nullable()->after('adjunto_path');
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn(['adjunto_path', 'adjunto_nombre']);
        });
    }
};
