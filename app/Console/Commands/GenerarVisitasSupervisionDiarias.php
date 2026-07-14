<?php

namespace App\Console\Commands;

use App\Models\CoordinadorOperador;
use App\Models\ServicioGeneralRutaInspeccion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenerarVisitasSupervisionDiarias extends Command
{
    protected $signature = 'supervision:generar-visitas {--fecha= : Fecha en formato Y-m-d}';
    protected $description = 'Genera una visita diaria por cada agencia con responsable';

    public function handle(): int
    {
        $fecha = $this->option('fecha') ? Carbon::parse($this->option('fecha'))->toDateString() : now()->toDateString();
        $tabla = CoordinadorOperador::resolveTableName();
        $apellido = CoordinadorOperador::hasResolvedColumn('apellido');
        $codigo = Schema::hasColumn('agencias', 'codigo') ? 'codigo' : 'agencia';
        $creadas = 0;

        $asignaciones = DB::table('coordinador_operador_agencia as coa')
            ->join($tabla . ' as co', 'co.id', '=', 'coa.coordinador_operador_id')
            ->join('agencias as a', 'a.id', '=', 'coa.agencia_id')
            ->whereRaw('(SELECT COUNT(*) FROM coordinador_operador_agencia x WHERE x.agencia_id = a.id) = 1')
            ->select('a.id as agencia_id', 'a.terminal', "a.{$codigo} as codigo", 'co.id as responsable_id', 'co.nombre', ...($apellido ? ['co.apellido'] : []))
            ->orderBy('co.id')->orderBy('a.id')->get();

        foreach ($asignaciones as $asignacion) {
            $clave = "visita:{$fecha}:{$asignacion->agencia_id}";
            $responsable = trim($asignacion->nombre . ' ' . ($asignacion->apellido ?? ''));
            $trabajo = ServicioGeneralRutaInspeccion::firstOrCreate(
                ['clave_generacion' => $clave],
                [
                    'agencia_id' => $asignacion->agencia_id,
                    'coordinador_operador_id' => $asignacion->responsable_id,
                    'responsable_nombre' => $responsable,
                    'tipo' => 'inspeccion',
                    'nombre' => 'Visita diaria - ' . ($asignacion->terminal ?: $asignacion->codigo),
                    'fecha' => $fecha,
                    'estado' => 'asignada',
                    'prioridad' => 'media',
                    'descripcion' => 'Visita diaria de supervisión y verificación de requisitos fijos.',
                    'generado_automaticamente' => true,
                    'metadata' => ['origen' => 'generacion_diaria'],
                ]
            );

            if ($trabajo->wasRecentlyCreated) {
                $trabajo->historial()->create([
                    'accion' => 'generada_automaticamente', 'estado_nuevo' => 'asignada',
                    'responsable_id' => $asignacion->responsable_id, 'responsable_nombre' => $responsable,
                    'observacion' => 'Visita diaria creada por el sistema.',
                ]);
                $creadas++;
            }
        }

        $this->info("Visitas creadas: {$creadas}. Fecha: {$fecha}.");
        return self::SUCCESS;
    }
}
