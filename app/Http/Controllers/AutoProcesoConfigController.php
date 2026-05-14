<?php

namespace App\Http\Controllers;

use App\Models\AutoProcesoConfig;
use Illuminate\Http\Request;

class AutoProcesoConfigController extends Controller
{
    public function show(string $sistema)
    {
        $this->validateSistema($sistema);

        $config = AutoProcesoConfig::firstOrCreate(
            ['sistema' => strtolower($sistema)],
            [
                'enabled' => false,
                'max_seconds' => 1800,
                'process_day_offset' => 0,
            ]
        );

        return response()->json($config);
    }

    public function update(Request $request, string $sistema)
    {
        $this->validateSistema($sistema);
        $sistema = strtolower($sistema);

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'hora' => ['nullable', 'date_format:H:i'],
            'correo' => ['nullable', 'email'],
            'max_seconds' => ['nullable', 'integer', 'min:60', 'max:7200'],
            'process_day_offset' => ['nullable', 'integer', 'in:0,-1'],
            'process_date' => ['nullable', 'date'],
        ]);

        if (!empty($validated['enabled'])) {
            if (empty($validated['hora']) || empty($validated['correo'])) {
                return response()->json([
                    'message' => 'Hora y correo son obligatorios cuando esta habilitado',
                ], 422);
            }
        }

        $config = AutoProcesoConfig::firstOrCreate(
            ['sistema' => $sistema],
            [
                'enabled' => false,
                'max_seconds' => 1800,
                'process_day_offset' => 0,
            ]
        );

        $newEnabled = (bool) $validated['enabled'];
        $newHora = $validated['hora'] ?? null;
        $newCorreo = $validated['correo'] ?? null;
        $newMaxSeconds = (int) ($validated['max_seconds'] ?? 1800);
        $newOffset = (int) ($validated['process_day_offset'] ?? 0);
        $newProcessDate = $validated['process_date'] ?? null;

        $currentHora = $config->hora ? substr((string) $config->hora, 0, 5) : null;
        $currentProcessDate = $config->process_date
            ? $config->process_date->format('Y-m-d')
            : null;
        $shouldResetLastRun =
            ((bool) $config->enabled !== $newEnabled)
            || ($currentHora !== $newHora)
            || (($config->correo ?? null) !== $newCorreo)
            || ((int) ($config->max_seconds ?? 1800) !== $newMaxSeconds)
            || ((int) ($config->process_day_offset ?? 0) !== $newOffset)
            || ($currentProcessDate !== $newProcessDate);

        $payload = [
            'enabled' => $newEnabled,
            'hora' => $newHora,
            'correo' => $newCorreo,
            'max_seconds' => $newMaxSeconds,
            'process_day_offset' => $newOffset,
            'process_date' => $newProcessDate,
        ];

        if ($shouldResetLastRun) {
            $payload['last_run_at'] = null;
            $payload['last_status'] = null;
            $payload['last_summary'] = null;
        }

        $config->fill($payload);
        $config->save();
        $config->refresh();

        return response()->json([
            'message' => 'Configuracion guardada correctamente',
            'config' => $config,
        ]);
    }

    private function validateSistema(string $sistema): void
    {
        if (!in_array(strtolower($sistema), ['lotobet', 'lotonet'], true)) {
            abort(404);
        }
    }
}
