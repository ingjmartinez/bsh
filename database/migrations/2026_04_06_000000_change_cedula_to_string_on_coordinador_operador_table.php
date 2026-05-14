<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE coordinador_operador MODIFY cedula VARCHAR(11) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE coordinador_operador MODIFY cedula BIGINT UNSIGNED NOT NULL');
    }
};
