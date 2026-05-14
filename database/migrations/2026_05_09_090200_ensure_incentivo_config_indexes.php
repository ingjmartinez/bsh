<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = DB::getDatabaseName();

        if (Schema::hasTable('incentivo_administrativos')) {
            DB::statement('ALTER TABLE incentivo_administrativos MODIFY grupo VARCHAR(70) NOT NULL, MODIFY nombre VARCHAR(120) NOT NULL, MODIFY empresa VARCHAR(50) NOT NULL');

            $uniqueExists = DB::table('information_schema.statistics')
                ->where('table_schema', $schema)
                ->where('table_name', 'incentivo_administrativos')
                ->where('index_name', 'uniq_incentivo_adm_grupo_nombre_empresa')
                ->exists();

            if (! $uniqueExists) {
                DB::statement('ALTER TABLE incentivo_administrativos ADD UNIQUE uniq_incentivo_adm_grupo_nombre_empresa (grupo, nombre, empresa)');
            }

            $indexExists = DB::table('information_schema.statistics')
                ->where('table_schema', $schema)
                ->where('table_name', 'incentivo_administrativos')
                ->where('index_name', 'incentivo_administrativos_grupo_empresa_index')
                ->exists();

            if (! $indexExists) {
                DB::statement('ALTER TABLE incentivo_administrativos ADD INDEX incentivo_administrativos_grupo_empresa_index (grupo, empresa)');
            }
        }

        if (Schema::hasTable('porcentaje_incentivos')) {
            $uniqueExists = DB::table('information_schema.statistics')
                ->where('table_schema', $schema)
                ->where('table_name', 'porcentaje_incentivos')
                ->where('index_name', 'porcentaje_incentivos_posicion_unique')
                ->exists();

            if (! $uniqueExists) {
                DB::statement('ALTER TABLE porcentaje_incentivos ADD UNIQUE porcentaje_incentivos_posicion_unique (posicion)');
            }
        }
    }

    public function down(): void
    {
        $schema = DB::getDatabaseName();

        if (Schema::hasTable('incentivo_administrativos')) {
            $uniqueExists = DB::table('information_schema.statistics')
                ->where('table_schema', $schema)
                ->where('table_name', 'incentivo_administrativos')
                ->where('index_name', 'uniq_incentivo_adm_grupo_nombre_empresa')
                ->exists();

            if ($uniqueExists) {
                DB::statement('ALTER TABLE incentivo_administrativos DROP INDEX uniq_incentivo_adm_grupo_nombre_empresa');
            }

            $indexExists = DB::table('information_schema.statistics')
                ->where('table_schema', $schema)
                ->where('table_name', 'incentivo_administrativos')
                ->where('index_name', 'incentivo_administrativos_grupo_empresa_index')
                ->exists();

            if ($indexExists) {
                DB::statement('ALTER TABLE incentivo_administrativos DROP INDEX incentivo_administrativos_grupo_empresa_index');
            }
        }

        if (Schema::hasTable('porcentaje_incentivos')) {
            $uniqueExists = DB::table('information_schema.statistics')
                ->where('table_schema', $schema)
                ->where('table_name', 'porcentaje_incentivos')
                ->where('index_name', 'porcentaje_incentivos_posicion_unique')
                ->exists();

            if ($uniqueExists) {
                DB::statement('ALTER TABLE porcentaje_incentivos DROP INDEX porcentaje_incentivos_posicion_unique');
            }
        }
    }
};
