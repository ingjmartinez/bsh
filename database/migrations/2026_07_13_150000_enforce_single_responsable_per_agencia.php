<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coordinador_operador_agencia')) {
            return;
        }

        $tablaPersonas = Schema::hasTable('coordinadores_operador')
            ? 'coordinadores_operador'
            : 'coordinador_operador';

        $duplicadas = DB::table('coordinador_operador_agencia')
            ->select('agencia_id')
            ->groupBy('agencia_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('agencia_id');

        foreach ($duplicadas as $agenciaId) {
            $asignaciones = DB::table('coordinador_operador_agencia')
                ->where('agencia_id', $agenciaId)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();
            $vigente = $asignaciones->first();
            $responsableVigente = $this->nombreResponsable($tablaPersonas, (int) $vigente->coordinador_operador_id);

            foreach ($asignaciones->skip(1) as $anterior) {
                if (Schema::hasTable('coordinador_operador_agencia_historial')) {
                    DB::table('coordinador_operador_agencia_historial')->insert([
                        'agencia_id' => $agenciaId,
                        'responsable_anterior_id' => $anterior->coordinador_operador_id,
                        'responsable_anterior_nombre' => $this->nombreResponsable($tablaPersonas, (int) $anterior->coordinador_operador_id),
                        'responsable_nuevo_id' => $vigente->coordinador_operador_id,
                        'responsable_nuevo_nombre' => $responsableVigente,
                        'user_id' => null,
                        'motivo' => 'Consolidación automática de una asignación duplicada heredada.',
                        'metadata' => json_encode(['origen' => 'restriccion_responsable_unico']),
                        'created_at' => now(),
                    ]);
                }

                DB::table('coordinador_operador_agencia')->where('id', $anterior->id)->delete();
            }
        }

        Schema::table('coordinador_operador_agencia', function (Blueprint $table) {
            $table->unique('agencia_id', 'coa_agencia_responsable_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('coordinador_operador_agencia')) {
            Schema::table('coordinador_operador_agencia', function (Blueprint $table) {
                $table->dropUnique('coa_agencia_responsable_unique');
            });
        }
    }

    private function nombreResponsable(string $tabla, int $id): ?string
    {
        $persona = DB::table($tabla)->where('id', $id)->first();
        if (!$persona) return null;

        return trim((string) ($persona->nombre ?? '') . ' ' . (string) ($persona->apellido ?? '')) ?: null;
    }
};
