<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tipos_solicitud_tecnologia')) {
            Schema::create('tipos_solicitud_tecnologia', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 80)->unique();
                $table->boolean('activo')->default(true);
                $table->boolean('requiere_progreso')->default(false);
                $table->timestamps();
            });
        }

        DB::table('tipos_solicitud_tecnologia')->updateOrInsert(
            ['nombre' => 'Averia'],
            ['activo' => true, 'requiere_progreso' => false, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('tipos_solicitud_tecnologia')->updateOrInsert(
            ['nombre' => 'Desarrollo'],
            ['activo' => true, 'requiere_progreso' => true, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_solicitud_tecnologia');
    }
};
