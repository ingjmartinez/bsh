<?php

namespace Database\Seeders;

use App\Models\DepartamentoCrm;
use App\Models\Tarea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TareasSeeder extends Seeder
{
    /**
     * Seed departamentos y tareas de ejemplo para pruebas.
     */
    public function run(): void
    {
        // ═══ Departamentos ═══
        $departamentos = [
            ['nombre' => 'Tecnología',    'descripcion' => 'Sistemas e infraestructura',  'color' => '#405189'],
            ['nombre' => 'Operaciones',   'descripcion' => 'Gestión de agencias',         'color' => '#0ab39c'],
            ['nombre' => 'Contabilidad',  'descripcion' => 'Finanzas y reportes',         'color' => '#f7b84b'],
            ['nombre' => 'RRHH',          'descripcion' => 'Recursos Humanos',            'color' => '#299cdb'],
            ['nombre' => 'Marketing',     'descripcion' => 'Promoción y publicidad',      'color' => '#f06548'],
        ];

        foreach ($departamentos as $depto) {
            DepartamentoCrm::updateOrCreate(
                ['nombre' => $depto['nombre']],
                $depto
            );
        }

        $deptos = DepartamentoCrm::all();
        $user = User::first();
        if (!$user) {
            $this->command->info('No hay usuarios. Crea un usuario primero.');
            return;
        }

        $now = Carbon::now();

        // ═══ Tareas de ejemplo ═══
        $tareas = [
            // Tecnología
            [
                'titulo'          => 'Implementar módulo de reportes',
                'descripcion'     => 'Crear los reportes de ventas mensuales con gráficas interactivas.',
                'departamento_id' => $deptos->where('nombre', 'Tecnología')->first()->id,
                'user_id'         => $user->id,
                'asignado_id'     => $user->id,
                'estado'          => 'en_progreso',
                'prioridad'       => 'alta',
                'progreso'        => 65,
                'fecha_inicio'    => $now->copy()->subDays(10)->toDateString(),
                'fecha_fin'       => $now->copy()->addDays(5)->toDateString(),
            ],
            [
                'titulo'          => 'Configurar backups automáticos',
                'descripcion'     => 'Programar respaldos diarios de la base de datos en servidor remoto.',
                'departamento_id' => $deptos->where('nombre', 'Tecnología')->first()->id,
                'user_id'         => $user->id,
                'asignado_id'     => $user->id,
                'estado'          => 'completada',
                'prioridad'       => 'critica',
                'progreso'        => 100,
                'fecha_inicio'    => $now->copy()->subDays(20)->toDateString(),
                'fecha_fin'       => $now->copy()->subDays(5)->toDateString(),
                'fecha_completada' => $now->copy()->subDays(4)->toDateTimeString(),
            ],
            [
                'titulo'          => 'Actualizar servidor PHP 8.3',
                'descripcion'     => 'Migrar el servidor de producción a PHP 8.3.',
                'departamento_id' => $deptos->where('nombre', 'Tecnología')->first()->id,
                'user_id'         => $user->id,
                'estado'          => 'pendiente',
                'prioridad'       => 'media',
                'progreso'        => 0,
                'fecha_inicio'    => $now->copy()->addDays(2)->toDateString(),
                'fecha_fin'       => $now->copy()->addDays(10)->toDateString(),
            ],
            // Operaciones
            [
                'titulo'          => 'Auditoría de agencias Zona Norte',
                'descripcion'     => 'Revisar faltantes y cierres de caja de las agencias del norte.',
                'departamento_id' => $deptos->where('nombre', 'Operaciones')->first()->id,
                'user_id'         => $user->id,
                'asignado_id'     => $user->id,
                'estado'          => 'en_progreso',
                'prioridad'       => 'alta',
                'progreso'        => 40,
                'fecha_inicio'    => $now->copy()->subDays(15)->toDateString(),
                'fecha_fin'       => $now->copy()->subDays(2)->toDateString(), // ATRASADA
            ],
            [
                'titulo'          => 'Capacitación nuevos operadores',
                'descripcion'     => 'Entrenar a los 5 operadores nuevos en el sistema de ventas.',
                'departamento_id' => $deptos->where('nombre', 'Operaciones')->first()->id,
                'user_id'         => $user->id,
                'estado'          => 'pendiente',
                'prioridad'       => 'media',
                'progreso'        => 0,
                'fecha_inicio'    => $now->copy()->addDays(1)->toDateString(),
                'fecha_fin'       => $now->copy()->addDays(8)->toDateString(),
            ],
            // Contabilidad
            [
                'titulo'          => 'Cierre contable mensual',
                'descripcion'     => 'Preparar y revisar el cierre contable del mes.',
                'departamento_id' => $deptos->where('nombre', 'Contabilidad')->first()->id,
                'user_id'         => $user->id,
                'asignado_id'     => $user->id,
                'estado'          => 'en_progreso',
                'prioridad'       => 'critica',
                'progreso'        => 80,
                'fecha_inicio'    => $now->copy()->subDays(5)->toDateString(),
                'fecha_fin'       => $now->copy()->addDays(1)->toDateString(),
            ],
            [
                'titulo'          => 'Conciliación bancaria',
                'descripcion'     => 'Verificar todas las transacciones bancarias vs sistema.',
                'departamento_id' => $deptos->where('nombre', 'Contabilidad')->first()->id,
                'user_id'         => $user->id,
                'estado'          => 'pendiente',
                'prioridad'       => 'alta',
                'progreso'        => 0,
                'fecha_inicio'    => $now->copy()->subDays(8)->toDateString(),
                'fecha_fin'       => $now->copy()->subDays(1)->toDateString(), // ATRASADA
            ],
            // RRHH
            [
                'titulo'          => 'Evaluación de desempeño Q2',
                'descripcion'     => 'Realizar evaluaciones de desempeño del segundo trimestre.',
                'departamento_id' => $deptos->where('nombre', 'RRHH')->first()->id,
                'user_id'         => $user->id,
                'estado'          => 'pendiente',
                'prioridad'       => 'media',
                'progreso'        => 0,
                'fecha_inicio'    => $now->copy()->addDays(3)->toDateString(),
                'fecha_fin'       => $now->copy()->addDays(20)->toDateString(),
            ],
            // Marketing
            [
                'titulo'          => 'Campaña promocional de verano',
                'descripcion'     => 'Diseñar y lanzar la campaña de descuentos de verano.',
                'departamento_id' => $deptos->where('nombre', 'Marketing')->first()->id,
                'user_id'         => $user->id,
                'asignado_id'     => $user->id,
                'estado'          => 'en_progreso',
                'prioridad'       => 'alta',
                'progreso'        => 30,
                'fecha_inicio'    => $now->copy()->subDays(3)->toDateString(),
                'fecha_fin'       => $now->copy()->addDays(14)->toDateString(),
            ],
            [
                'titulo'          => 'Rediseño de redes sociales',
                'descripcion'     => 'Actualizar imágenes de perfil y banners en todas las plataformas.',
                'departamento_id' => $deptos->where('nombre', 'Marketing')->first()->id,
                'user_id'         => $user->id,
                'estado'          => 'completada',
                'prioridad'       => 'baja',
                'progreso'        => 100,
                'fecha_inicio'    => $now->copy()->subDays(14)->toDateString(),
                'fecha_fin'       => $now->copy()->subDays(7)->toDateString(),
                'fecha_completada' => $now->copy()->subDays(7)->toDateTimeString(),
            ],
        ];

        foreach ($tareas as $tarea) {
            Tarea::updateOrCreate(
                ['titulo' => $tarea['titulo']],
                $tarea
            );
        }

        $this->command->info('✅ Departamentos y tareas de ejemplo creados exitosamente.');
    }
}
