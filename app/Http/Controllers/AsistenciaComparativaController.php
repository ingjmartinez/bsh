<?php

namespace App\Http\Controllers;

use App\Mail\AsistenciaComparativaCoordinadorMail;
use App\Models\Agencia;
use App\Models\CoordinadorOperador;
use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AsistenciaComparativaController extends Controller
{
    public function index(Request $request)
    {
        $coordinadores = CoordinadorOperador::query()
            ->where('activo', true)
            ->selectRaw("TRIM(COALESCE(nombre, '')) as nombre_completo")
            ->whereRaw("TRIM(COALESCE(nombre, '')) <> ''")
            ->distinct()
            ->orderBy('nombre_completo')
            ->pluck('nombre_completo')
            ->values();

        return view('agencias.asistencia_comparativa', [
            'coordinadores' => $coordinadores,
        ]);
    }

    public function list(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        $soloIncumplidas = $request->input('solo_incumplidas', '1') === '1';
        $coordinador = trim((string) $request->input('coordinador', ''));

        try {
            $rows = $this->generarListado($fecha, $soloIncumplidas, $coordinador);

            return response()->json([
                'fecha' => $fecha,
                'total' => count($rows),
                'incumplidas' => collect($rows)->where('incumplida', true)->count(),
                'data' => array_values($rows),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => [],
            ], 422);
        }
    }

    public function enviarPorCoordinador(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'coordinador' => ['required', 'string', 'max:150'],
            'solo_incumplidas' => ['nullable', 'in:0,1'],
        ]);

        $coordinadorNombre = trim((string) $validated['coordinador']);
        $fecha = (string) $validated['fecha'];
        $soloIncumplidas = (($validated['solo_incumplidas'] ?? '1') === '1');

        try {
            $rows = $this->generarListado($fecha, $soloIncumplidas, $coordinadorNombre);

            if (empty($rows)) {
                return response()->json([
                    'message' => 'No hay datos para enviar con los filtros seleccionados.',
                ], 422);
            }

            $correos = CoordinadorOperador::query()
                ->where('activo', true)
                ->whereRaw("TRIM(COALESCE(nombre, '')) = ?", [$coordinadorNombre])
                ->whereNotNull('email')
                ->whereRaw("TRIM(email) <> ''")
                ->pluck('email')
                ->map(fn($correo) => trim((string) $correo))
                ->filter()
                ->unique()
                ->values();

            if ($correos->isEmpty()) {
                return response()->json([
                    'message' => 'El coordinador seleccionado no tiene correo registrado.',
                ], 422);
            }

            $payload = [
                'coordinador' => $coordinadorNombre,
                'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
                'rows' => $rows,
            ];

            Mail::to($correos->all())->send(new AsistenciaComparativaCoordinadorMail($payload));

            return response()->json([
                'message' => 'Reporte enviado correctamente a: ' . $correos->implode(', '),
                'total' => count($rows),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function generarListado(string $fecha, bool $soloIncumplidas, string $coordinador = ''): array
    {
        $agencias = Agencia::query()
            ->with([
                'coordinadoresOperadores' => function ($query) {
                    $query->where('activo', true)
                        ->select('coordinadores_operador.id', 'nombre', 'email');
                }
            ])
                ->select('id', 'codigo as agencia', 'nombre as nombre_agencia', 'terminal', 'horario_am', 'horario_pm')
                ->whereNotNull('terminal')
                ->where(function ($q) {
                    $q->whereNotNull('horario_am')
                        ->orWhereNotNull('horario_pm');
                })
            ->when($coordinador !== '', function ($query) use ($coordinador) {
                $query->whereHas('coordinadoresOperadores', function ($subQuery) use ($coordinador) {
                    $subQuery->where('activo', true)
                        ->whereRaw("TRIM(COALESCE(nombre, '')) = ?", [$coordinador]);
                });
            })
                ->get();

        $mapAsistencia = $this->consolidarAsistenciasPorTerminalDesdeApi($fecha);

        $rows = [];

        foreach ($agencias as $agencia) {
                $coordinadoresAgencia = $agencia->coordinadoresOperadores
                    ->map(function ($item) {
                        return trim((string) ($item->nombre ?? ''));
                    })
                    ->filter()
                    ->unique()
                    ->values();

                $coordinadorAgencia = $coordinadoresAgencia->isNotEmpty()
                    ? $coordinadoresAgencia->implode(', ')
                    : '-';

                $terminalKey = $this->normalizarTerminal($agencia->terminal);
                $asistencia = $mapAsistencia[$terminalKey] ?? null;

                $entradaAmProgramada = $this->extraerHoraInicio($agencia->horario_am);
                $salidaAmProgramada = $this->extraerHoraFin($agencia->horario_am);
                $entradaPmProgramada = $this->extraerHoraInicio($agencia->horario_pm);
                $salidaPmProgramada = $this->extraerHoraFin($agencia->horario_pm);

                $entradaProgramada = $entradaAmProgramada ?: $entradaPmProgramada;
                $salidaProgramada = $salidaPmProgramada ?: $salidaAmProgramada;

                $entradaAmProgramadaDateTime = $this->parseFechaHora($fecha, $entradaAmProgramada);
                $salidaAmProgramadaDateTime = $this->parseFechaHora($fecha, $salidaAmProgramada);
                $entradaPmProgramadaDateTime = $this->parseFechaHora($fecha, $entradaPmProgramada);
                $salidaPmProgramadaDateTime = $this->parseFechaHora($fecha, $salidaPmProgramada);

                $entradaProgramadaDateTime = $this->parseFechaHora($fecha, $entradaProgramada);
                $salidaProgramadaDateTime = $this->parseFechaHora($fecha, $salidaProgramada);

                $entradasReales = $this->parsearHorasReales($asistencia['entradas'] ?? []);
                $salidasReales = $this->parsearHorasReales($asistencia['salidas'] ?? []);

                $entradaReal = $entradasReales[0] ?? null;
                $salidaReal = !empty($salidasReales) ? $salidasReales[array_key_last($salidasReales)] : null;

                $salidaAmReal = $this->seleccionarHoraCercana(
                    $salidasReales,
                    $salidaAmProgramadaDateTime,
                    $entradaAmProgramadaDateTime,
                    $entradaPmProgramadaDateTime
                );

                $entradaPmReal = $this->seleccionarHoraCercana(
                    $entradasReales,
                    $entradaPmProgramadaDateTime,
                    $salidaAmProgramadaDateTime,
                    $salidaPmProgramadaDateTime
                );

                $incumpleEntrada = false;
                $incumpleSalida = false;
                $minutosTarde = 0;
                $minutosSalidaAntes = 0;
                $observaciones = [];

                if ($entradaProgramadaDateTime && $entradaReal) {
                    if ($entradaReal->greaterThan($entradaProgramadaDateTime)) {
                        $incumpleEntrada = true;
                        $minutosTarde = $entradaProgramadaDateTime->diffInMinutes($entradaReal);
                        $observaciones[] = 'Entrada tardía';
                    }
                } elseif ($entradaProgramadaDateTime && !$entradaReal) {
                    $incumpleEntrada = true;
                    $observaciones[] = 'Sin registro de entrada';
                }

                if ($salidaProgramadaDateTime && $salidaReal) {
                    if ($salidaReal->lessThan($salidaProgramadaDateTime)) {
                        $incumpleSalida = true;
                        $minutosSalidaAntes = $salidaReal->diffInMinutes($salidaProgramadaDateTime);
                        $observaciones[] = 'Salida anticipada';
                    }
                } elseif ($salidaProgramadaDateTime && !$salidaReal) {
                    $incumpleSalida = true;
                    $observaciones[] = 'Sin registro de salida';
                }

                $incumplida = $incumpleEntrada || $incumpleSalida;

                if ($soloIncumplidas && !$incumplida) {
                    continue;
                }

                $rows[] = [
                    'agencia_id' => $agencia->id,
                    'agencia' => $agencia->agencia,
                    'nombre_agencia' => $agencia->nombre_agencia,
                    'coordinador' => $coordinadorAgencia,
                    'terminal' => $agencia->terminal,
                    'horario_am' => $agencia->horario_am,
                    'horario_pm' => $agencia->horario_pm,
                    'entrada_am_programada' => $entradaAmProgramada,
                    'salida_am_programada' => $salidaAmProgramada,
                    'entrada_pm_programada' => $entradaPmProgramada,
                    'salida_pm_programada' => $salidaPmProgramada,
                    'entrada_programada' => $entradaProgramada,
                    'salida_programada' => $salidaProgramada,
                    'entrada_real' => $entradaReal ? $entradaReal->format('h:i A') : '-',
                    'salida_am_real' => $salidaAmReal ? $salidaAmReal->format('h:i A') : '-',
                    'entrada_pm_real' => $entradaPmReal ? $entradaPmReal->format('h:i A') : '-',
                    'salida_real' => $salidaReal ? $salidaReal->format('h:i A') : '-',
                    'minutos_tarde' => $minutosTarde,
                    'minutos_salida_antes' => $minutosSalidaAntes,
                    'incumple_entrada' => $incumpleEntrada,
                    'incumple_salida' => $incumpleSalida,
                    'incumplida' => $incumplida,
                    'estado' => $incumplida ? 'INCUMPLE' : 'CUMPLE',
                    'observaciones' => empty($observaciones) ? 'Cumple horario' : implode(' | ', $observaciones),
                    'fuente' => $asistencia['fuente'] ?? '-',
                ];
            }

        return $rows;
    }

    private function consolidarAsistenciasPorTerminalDesdeApi(string $fecha): array
    {
        $bet = $this->obtenerAsistenciaLotobetApi($fecha);
        $net = $this->obtenerAsistenciaLotonetApi($fecha);

        $map = [];

        foreach ($bet as $row) {
            $terminalRaw = (string) ($row['agencia'] ?? $row['agencia_id'] ?? '');
            $terminalKey = $this->normalizarTerminal($terminalRaw);

            if (!isset($map[$terminalKey])) {
                $map[$terminalKey] = [
                    'entrada' => null,
                    'salida' => null,
                    'entradas' => [],
                    'salidas' => [],
                    'has_bet' => false,
                    'has_net' => false,
                    'fuente' => '-',
                ];
            }

            $entrada = $row['primer_login'] ?? $row['entrada'] ?? null;
            $salida = $row['ultimo_logout'] ?? $row['ultimo_login'] ?? $row['salida'] ?? null;

            if ($entrada) {
                $map[$terminalKey]['entradas'][] = $entrada;
                if (!$map[$terminalKey]['entrada'] || Carbon::parse($entrada)->lessThan(Carbon::parse($map[$terminalKey]['entrada']))) {
                    $map[$terminalKey]['entrada'] = $entrada;
                }
            }

            if ($salida) {
                $map[$terminalKey]['salidas'][] = $salida;
                if (!$map[$terminalKey]['salida'] || Carbon::parse($salida)->greaterThan(Carbon::parse($map[$terminalKey]['salida']))) {
                    $map[$terminalKey]['salida'] = $salida;
                }
            }

            $map[$terminalKey]['has_bet'] = true;
        }

        foreach ($net as $row) {
            $terminalRaw = (string) ($row['agencia'] ?? $row['terminal'] ?? $row['agencia_id'] ?? '');
            if (trim($terminalRaw) === '') {
                continue;
            }

            $terminalKey = $this->normalizarTerminal($terminalRaw);

            if (!isset($map[$terminalKey])) {
                $map[$terminalKey] = [
                    'entrada' => null,
                    'salida' => null,
                    'entradas' => [],
                    'salidas' => [],
                    'has_bet' => false,
                    'has_net' => false,
                    'fuente' => '-',
                ];
            }

            $entrada = $row['entrada'] ?? $row['primer_login'] ?? null;
            $salida = $row['salida'] ?? $row['ultimo_logout'] ?? null;

            if ($entrada) {
                $map[$terminalKey]['entradas'][] = $entrada;
                if (!$map[$terminalKey]['entrada'] || Carbon::parse($entrada)->lessThan(Carbon::parse($map[$terminalKey]['entrada']))) {
                    $map[$terminalKey]['entrada'] = $entrada;
                }
            }

            if ($salida) {
                $map[$terminalKey]['salidas'][] = $salida;
                if (!$map[$terminalKey]['salida'] || Carbon::parse($salida)->greaterThan(Carbon::parse($map[$terminalKey]['salida']))) {
                    $map[$terminalKey]['salida'] = $salida;
                }
            }

            $map[$terminalKey]['has_net'] = true;
        }

        foreach ($map as $terminalKey => $row) {
            $map[$terminalKey]['fuente'] = $row['has_bet'] && $row['has_net']
                ? 'BET/NET'
                : ($row['has_bet'] ? 'BET' : 'NET');
        }

        return $map;
    }

    private function obtenerAsistenciaLotobetApi(string $fecha): array
    {
        $token = Token::find(1);

        if (!$token) {
            throw new \RuntimeException('Genere un token de Lotobet.');
        }

        if (now()->greaterThan($token->fecha)) {
            throw new \RuntimeException('El token de Lotobet expiró, genere uno nuevo.');
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://ltkadapi.lotobet.bet/api/V1/var4XZ3ojQiPZq5BpI/{$token->token}/{$fecha}/05",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'AhfCC: yB0tt5KW3wVVCYYtCpen',
                'AhfVB: xSzdgtOKbGRhUhtv1ois',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $payload = json_decode((string) $response, true);
        $code = isset($payload['code']) ? (string) $payload['code'] : null;
        if ($code !== null && !in_array(strtolower(trim($code)), ['0', '00', '200', 'ok', 'success'], true)) {
            $msg = $payload['msg'] ?? $payload['message'] ?? 'Respuesta inválida de API Lotobet';
            throw new \RuntimeException($msg);
        }

        return $payload['Content'] ?? [];
    }

    private function obtenerAsistenciaLotonetApi(string $fecha): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/asistencia_usuarios/{$fecha}/5",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
                "usuario": {
                    "username": "fjoselito",
                    "password": "mnXd5pSyF3HXjCC4"
                }
            }',
            CURLOPT_HTTPHEADER => [
                'token: ZFozLWdBYyqERusVdTsW',
                'Content-Type: application/json',
                'Cookie: _orkapi_session=RkZLWFpIMnM1UTdUdjRXVzNuMFRmZFZnQ2U5N0JoV0JaSzBheUFlZ21TSVoyUEhWWFc2Y2R4Nzd2SmVhQXJKOGtsSktHWnNmelgzWGsxcmJESEVkcXRlWW5tdGpzU1ZZcXRBZFNva2lqL3pGMFppZFZnZUxPUXBscWxLYVdVcUwzdURYb1V5bGJwanZkeDdJTGUzZndkV3FxNmtiMjdvNkxpU0ZQK2RWRU1nPS0tbkVwL215TXpYTXpLS1lYYXJTR3Y2UT09--7e272c2a327d71d9feb7996870d828122936b682',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $payload = json_decode((string) $response, true);
        $code = isset($payload['code']) ? (string) $payload['code'] : null;
        if ($code !== null && !in_array(strtolower(trim($code)), ['0', '00', '200', 'ok', 'success'], true)) {
            $msg = $payload['msg'] ?? $payload['message'] ?? 'Respuesta inválida de API Lotonet';
            throw new \RuntimeException($msg);
        }

        return $payload['data']['result'] ?? [];
    }

    private function normalizarTerminal(?string $terminal): string
    {
        if (!$terminal) {
            return '0';
        }

        $valor = ltrim(trim($terminal), '0');
        return $valor === '' ? '0' : $valor;
    }

    private function extraerHoraInicio(?string $horario): ?string
    {
        if (!$horario || !str_contains($horario, '/')) {
            return null;
        }

        $partes = explode('/', $horario);
        return isset($partes[0]) ? trim($partes[0]) : null;
    }

    private function extraerHoraFin(?string $horario): ?string
    {
        if (!$horario || !str_contains($horario, '/')) {
            return null;
        }

        $partes = explode('/', $horario);
        return isset($partes[1]) ? trim($partes[1]) : null;
    }

    private function parseFechaHora(string $fecha, ?string $hora): ?Carbon
    {
        if (!$hora) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d g:i A', $fecha . ' ' . strtoupper($hora));
        } catch (Throwable $e) {
            return null;
        }
    }

    private function parsearHorasReales(array $horas): array
    {
        $parsed = [];

        foreach ($horas as $hora) {
            if (!$hora) {
                continue;
            }

            try {
                $parsed[] = Carbon::parse($hora);
            } catch (Throwable $e) {
                // Ignorar valores no parseables
            }
        }

        usort($parsed, fn (Carbon $a, Carbon $b) => $a->getTimestamp() <=> $b->getTimestamp());

        return $parsed;
    }

    private function seleccionarHoraCercana(array $horas, ?Carbon $objetivo, ?Carbon $desde = null, ?Carbon $hasta = null): ?Carbon
    {
        $filtradas = array_values(array_filter($horas, function (Carbon $hora) use ($desde, $hasta) {
            if ($desde && $hora->lessThan($desde)) {
                return false;
            }

            if ($hasta && $hora->greaterThan($hasta)) {
                return false;
            }

            return true;
        }));

        if (empty($filtradas)) {
            return null;
        }

        if (!$objetivo) {
            return $filtradas[0];
        }

        usort($filtradas, fn (Carbon $a, Carbon $b) => abs($a->diffInSeconds($objetivo, false)) <=> abs($b->diffInSeconds($objetivo, false)));

        return $filtradas[0] ?? null;
    }
}
