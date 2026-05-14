<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('departamentos')) {
            Schema::create('departamentos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100)->unique();
                $table->string('codigo', 30)->nullable()->unique();
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('departamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('departamentos', 'descripcion')) {
                $table->string('descripcion', 255)->nullable()->after('codigo');
            }

            if (!Schema::hasColumn('departamentos', 'color')) {
                $table->string('color', 7)->default('#405189')->after('descripcion');
            }
        });

        $departamentos = [
            ['nombre' => 'Tecnologia', 'codigo' => 'TEC', 'descripcion' => 'Sistemas e infraestructura', 'color' => '#405189'],
            ['nombre' => 'Operaciones', 'codigo' => 'OPE', 'descripcion' => 'Gestion de agencias', 'color' => '#0ab39c'],
            ['nombre' => 'Contabilidad', 'codigo' => 'CON', 'descripcion' => 'Finanzas y reportes', 'color' => '#f7b84b'],
            ['nombre' => 'RRHH', 'codigo' => 'RRHH', 'descripcion' => 'Recursos Humanos', 'color' => '#299cdb'],
            ['nombre' => 'Marketing', 'codigo' => 'MKT', 'descripcion' => 'Promocion y publicidad', 'color' => '#f06548'],
        ];

        foreach ($departamentos as $departamento) {
            DB::table('departamentos')->updateOrInsert(
                ['nombre' => $departamento['nombre']],
                array_merge($departamento, [
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        Schema::table('departamentos', function (Blueprint $table) {
            if (Schema::hasColumn('departamentos', 'color')) {
                $table->dropColumn('color');
            }

            if (Schema::hasColumn('departamentos', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
        });
    }
};
