<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coordinador_operador', function (Blueprint $table) {
            $table->string('correo', 150)->nullable()->after('apellido');
            $table->unsignedBigInteger('telefono')->nullable()->after('cedula');
        });
    }

    public function down(): void
    {
        Schema::table('coordinador_operador', function (Blueprint $table) {
            $table->dropColumn(['correo', 'telefono']);
        });
    }
};
