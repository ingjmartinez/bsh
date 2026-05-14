<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$database = (string) config('database.connections.mysql.database');
$timestamp = now()->format('Y-m-d H:i:s');
$sessionLifetime = (int) config('session.lifetime', 120);

/**
 * Lee una propiedad de stdClass tolerando mayúsculas/minúsculas del driver.
 */
function field(object $row, string ...$keys)
{
    foreach ($keys as $key) {
        if (property_exists($row, $key)) {
            return $row->{$key};
        }
    }

    $array = (array) $row;
    foreach ($keys as $key) {
        foreach ($array as $k => $value) {
            if (strcasecmp((string) $k, $key) === 0) {
                return $value;
            }
        }
    }

    return null;
}

$tables = DB::select(
    'SELECT
        t.table_name,
        t.engine,
        t.table_rows,
        ROUND((t.data_length + t.index_length) / 1024 / 1024, 2) AS size_mb,
        ROUND(t.data_length / 1024 / 1024, 2) AS data_mb,
        ROUND(t.index_length / 1024 / 1024, 2) AS index_mb,
        t.table_collation
     FROM information_schema.tables t
     WHERE t.table_schema = ?
     ORDER BY (t.data_length + t.index_length) DESC',
    [$database]
);

$indexStats = DB::select(
    'SELECT
        s.table_name,
        COUNT(DISTINCT s.index_name) AS total_indexes,
        MAX(CASE WHEN s.index_name = \'PRIMARY\' THEN 1 ELSE 0 END) AS has_primary
     FROM information_schema.statistics s
     WHERE s.table_schema = ?
     GROUP BY s.table_name',
    [$database]
);

$indexByTable = [];
foreach ($indexStats as $indexRow) {
    $tableName = (string) field($indexRow, 'table_name');
    $indexByTable[$tableName] = [
        'total_indexes' => (int) field($indexRow, 'total_indexes'),
        'has_primary' => (int) field($indexRow, 'has_primary'),
    ];
}

$tablesNoPrimary = DB::select(
    'SELECT t.table_name
     FROM information_schema.tables t
     LEFT JOIN information_schema.statistics s
       ON s.table_schema = t.table_schema
      AND s.table_name = t.table_name
      AND s.index_name = \'PRIMARY\'
     WHERE t.table_schema = ?
       AND t.table_type = \'BASE TABLE\'
       AND s.index_name IS NULL
     ORDER BY t.table_name',
    [$database]
);

$migrationFiles = collect(glob(base_path('database/migrations/*.php')))->map(function (string $file) {
    return basename($file);
});

$migrationRows = DB::table('migrations')->count();
$tableCount = count($tables);

$bigTables = array_values(array_filter($tables, static fn ($table) => (float) field($table, 'size_mb') >= 500));
$myIsamTables = array_values(array_filter($tables, static fn ($table) => strtoupper((string) field($table, 'engine')) === 'MYISAM'));
$lowIndexBigTables = array_values(array_filter($tables, function ($table) use ($indexByTable) {
    $name = (string) field($table, 'table_name');
    $size = (float) field($table, 'size_mb');
    $indexes = $indexByTable[$name]['total_indexes'] ?? 0;

    return $size >= 100 && $indexes <= 1;
}));

$lines = [];
$lines[] = 'INVENTARIO TECNICO DE BASE DE DATOS';
$lines[] = 'Generado: ' . $timestamp;
$lines[] = 'Base de datos: ' . $database;
$lines[] = str_repeat('=', 100);
$lines[] = '';
$lines[] = '1) RESUMEN GENERAL';
$lines[] = '- Total de tablas en DB: ' . $tableCount;
$lines[] = '- Total de migraciones ejecutadas (tabla migrations): ' . $migrationRows;
$lines[] = '- Total de archivos de migracion en repo: ' . $migrationFiles->count();
$lines[] = '- Session lifetime (min): ' . $sessionLifetime;
$lines[] = '';
$lines[] = 'Hallazgo principal: la base esta en estado hibrido (muchas tablas manuales fuera de migraciones).';
$lines[] = '';
$lines[] = '2) TABLAS MAS GRANDES (Top 20 por tamano)';
$lines[] = 'tabla | engine | rows | size_mb | data_mb | index_mb | total_indexes | has_primary';
$lines[] = str_repeat('-', 100);

$topTables = array_slice($tables, 0, 20);
foreach ($topTables as $table) {
    $name = (string) field($table, 'table_name');
    $indexes = $indexByTable[$name]['total_indexes'] ?? 0;
    $hasPrimary = $indexByTable[$name]['has_primary'] ?? 0;
    $lines[] = implode(' | ', [
        $name,
        (string) field($table, 'engine'),
        (string) field($table, 'table_rows'),
        (string) field($table, 'size_mb'),
        (string) field($table, 'data_mb'),
        (string) field($table, 'index_mb'),
        (string) $indexes,
        (string) $hasPrimary,
    ]);
}

$lines[] = '';
$lines[] = '3) TABLAS GRANDES (>= 500MB)';
if (empty($bigTables)) {
    $lines[] = '- No hay tablas en este rango.';
} else {
    foreach ($bigTables as $table) {
        $lines[] = '- ' . field($table, 'table_name') . ' (' . field($table, 'size_mb') . ' MB, engine ' . field($table, 'engine') . ')';
    }
}

$lines[] = '';
$lines[] = '4) TABLAS CON ENGINE MyISAM (recomendado migrar a InnoDB)';
if (empty($myIsamTables)) {
    $lines[] = '- No se encontraron tablas MyISAM.';
} else {
    foreach ($myIsamTables as $table) {
        $lines[] = '- ' . field($table, 'table_name') . ' (' . field($table, 'size_mb') . ' MB)';
    }
}

$lines[] = '';
$lines[] = '5) TABLAS SIN PRIMARY KEY';
if (empty($tablesNoPrimary)) {
    $lines[] = '- Todas las tablas tienen PK.';
} else {
    foreach ($tablesNoPrimary as $table) {
        $lines[] = '- ' . field($table, 'table_name');
    }
}

$lines[] = '';
$lines[] = '6) TABLAS GRANDES CON POCOS INDICES (>=100MB y <=1 indice)';
if (empty($lowIndexBigTables)) {
    $lines[] = '- No se encontraron candidatos en este criterio.';
} else {
    foreach ($lowIndexBigTables as $table) {
        $tableName = (string) field($table, 'table_name');
        $lines[] = '- ' . $tableName . ' (' . field($table, 'size_mb') . ' MB, indexes: ' . ($indexByTable[$tableName]['total_indexes'] ?? 0) . ')';
    }
}

$lines[] = '';
$lines[] = '7) GUIA DE NORMALIZACION POR FASES (SIN PARAR EL SISTEMA)';
$lines[] = 'Fase 0 - Backup y control:';
$lines[] = '- Hacer backup completo (estructura + data).';
$lines[] = '- Congelar cambios de estructura manuales.';
$lines[] = '';
$lines[] = 'Fase 1 - Baseline de esquema:';
$lines[] = '- Ejecutar y guardar: php artisan db:show --counts';
$lines[] = '- Ejecutar y guardar: php artisan migrate:status';
$lines[] = '- Crear documento maestro de tablas (este inventario).';
$lines[] = '';
$lines[] = 'Fase 2 - Tablas core de la app (prioridad alta):';
$lines[] = '- users, roles/permisos, agencias, rutas, operador_ruta, reporte_diario_rutas, tareas, coordinador_operador.';
$lines[] = '- Asegurar migraciones para estructura completa.';
$lines[] = '- Unificar engine a InnoDB en tablas transaccionales.';
$lines[] = '';
$lines[] = 'Fase 3 - Indices de rendimiento (prioridad media):';
$lines[] = '- Crear migraciones solo de indices para tablas de consulta frecuente.';
$lines[] = '- Empezar por columnas de filtro real (fecha, agencia_id, cedula, ruta_id, producto_id).';
$lines[] = '- Aplicar primero en staging y medir.';
$lines[] = '';
$lines[] = 'Fase 4 - Historicas masivas (prioridad controlada):';
$lines[] = '- vt_usuarios_*, ventas_producto_*, premios_*, recargas_*, pagos_*.';
$lines[] = '- No recrear de cero en produccion: hacer cambios incrementales y por ventana.';
$lines[] = '';
$lines[] = 'Fase 5 - Regla de oro hacia adelante:';
$lines[] = '- Ningun cambio estructural sin migracion.';
$lines[] = '- Todo indice nuevo debe venir en migracion con nombre claro.';
$lines[] = '';
$lines[] = '8) SI QUIERES RECONSTRUIR TODO DESDE CERO (RECOMENDACION REALISTA)';
$lines[] = '- Hazlo por dominios, no todo junto.';
$lines[] = '- Primero core transaccional (app interna).';
$lines[] = '- Segundo historico/BI en procesos separados.';
$lines[] = '- Mantener ETL/replicacion durante la transicion.';
$lines[] = '';
$lines[] = '9) PROXIMO PASO SUGERIDO';
$lines[] = '- Generar un Plan de Migracion por tabla (prioridad, riesgo, downtime esperado, SQL de rollback).';

$outputPath = base_path('database_inventario.txt');
file_put_contents($outputPath, implode(PHP_EOL, $lines) . PHP_EOL);

echo 'Inventario generado en: ' . $outputPath . PHP_EOL;
