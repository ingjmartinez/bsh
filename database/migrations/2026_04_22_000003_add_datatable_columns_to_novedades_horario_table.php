<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('novedades_horario')) {
            return;
        }

        $columns = [
            'terminal' => fn (Blueprint $table) => $table->string('terminal', 50)->nullable()->after('id'),
            'nombre_agencia' => fn (Blueprint $table) => $table->string('nombre_agencia', 180)->nullable()->after('terminal'),
            'ruta' => fn (Blueprint $table) => $table->string('ruta', 120)->nullable()->after('nombre_agencia'),
            'nombre_empleado' => fn (Blueprint $table) => $table->string('nombre_empleado', 180)->nullable()->after('ruta'),
            'cedula' => fn (Blueprint $table) => $table->string('cedula', 25)->nullable()->after('nombre_empleado'),
            'fecha' => fn (Blueprint $table) => $table->date('fecha')->nullable()->after('cedula'),
            'ultimo_login' => fn (Blueprint $table) => $table->dateTime('ultimo_login')->nullable()->after('fecha'),
            'horas_acumuladas' => fn (Blueprint $table) => $table->decimal('horas_acumuladas', 10, 2)->default(0)->after('ultimo_login'),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('novedades_horario', $column)) {
                Schema::table('novedades_horario', $definition);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('novedades_horario')) {
            return;
        }

        foreach (['horas_acumuladas', 'ultimo_login', 'fecha', 'cedula', 'nombre_empleado', 'ruta', 'nombre_agencia', 'terminal'] as $column) {
            if (Schema::hasColumn('novedades_horario', $column)) {
                Schema::table('novedades_horario', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
