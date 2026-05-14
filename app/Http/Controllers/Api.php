<?php

namespace App\Http\Controllers;

use App\Models\CentroDeCosto;
use App\Models\CuentaContable;
use App\Models\DetalleCuenta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Api extends Controller
{
    // public function getCuentas()
    // {
    //     // header('Content-Type: application/json');

    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://apisj.azurewebsites.net/ApiSJ/CatalagoCta/Listar?strToken=87eb2d56-25f3-4d46-9cb0-73c07a550bd2&intIdEmpresa=168&strFiltros=[[%22AceptaMov%22%2C%20%221%22]]',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_HTTPHEADER => [
    //             'Content-Type: application/json'
    //         ],
    //     ));

    //     $response = curl_exec($curl);

    //     curl_close($curl);
    //     echo $response;
    // }

    public function getCuentas()
    {
        if (CuentaContable::query()->count() === 0) {
            $this->syncCuentasFromExternal();
        }

        $cuentas = CuentaContable::query()
            ->orderBy('cuenta')
            ->get()
            ->map(function (CuentaContable $cuenta) {
                return [
                    'id' => $cuenta->id,
                    'CUENTA' => $cuenta->cuenta,
                    'DESCRIPCION' => $cuenta->descripcion,
                    'CTACONTROL' => $cuenta->ctacontrol,
                    'TIPO' => $cuenta->tipo,
                ];
            });

        return response()->json($cuentas);
    }

    public function storeCuenta(Request $request)
    {
        $validated = $request->validate([
            'cuenta' => 'required|string|max:50|unique:cuentas_contables,cuenta',
            'descripcion' => 'required|string|max:255',
            'ctacontrol' => 'nullable|string|max:50',
            'tipo' => 'nullable|string|max:50',
        ]);

        $cuenta = CuentaContable::create($validated);

        return response()->json([
            'message' => 'Cuenta creada correctamente.',
            'data' => $cuenta,
        ], 201);
    }

    public function updateCuenta(Request $request, int $id)
    {
        $cuenta = CuentaContable::findOrFail($id);

        $validated = $request->validate([
            'cuenta' => 'required|string|max:50|unique:cuentas_contables,cuenta,' . $cuenta->id,
            'descripcion' => 'required|string|max:255',
            'ctacontrol' => 'nullable|string|max:50',
            'tipo' => 'nullable|string|max:50',
        ]);

        $cuenta->update($validated);

        return response()->json([
            'message' => 'Cuenta actualizada correctamente.',
            'data' => $cuenta,
        ]);
    }

    public function destroyCuenta(int $id)
    {
        $cuenta = CuentaContable::findOrFail($id);
        $cuenta->delete();

        return response()->json([
            'message' => 'Cuenta eliminada correctamente.',
        ]);
    }

    public function syncCuentas()
    {
        $syncResult = $this->syncCuentasFromExternal();

        if (!$syncResult['ok']) {
            return response()->json([
                'message' => $syncResult['message'],
                'status' => $syncResult['status'] ?? null,
                'error' => $syncResult['error'] ?? null,
            ], $syncResult['status'] ?? 500);
        }

        return response()->json([
            'message' => 'Sincronización completada correctamente.',
            'total_recibidas' => $syncResult['total_recibidas'],
            'creadas' => $syncResult['creadas'],
            'actualizadas' => $syncResult['actualizadas'],
            'omitidas' => $syncResult['omitidas'],
        ]);
    }


    public function getCentrosCosto()
    {
        $centros = CentroDeCosto::query()
            ->orderBy('id_centro_costo')
            ->get()
            ->map(function (CentroDeCosto $c) {
                return [
                    'id' => $c->id,
                    'IdCentroCosto' => $c->id_centro_costo,
                    'CompanyID' => $c->company_id,
                    'Descripcion' => $c->descripcion,
                    'Cuenta' => $c->cuenta,
                    'Inactivo' => $c->inactivo,
                    'Activo' => $c->activo,
                    'IdGrupo' => $c->id_grupo,
                    'IdSubGrupo' => $c->id_sub_grupo,
                    'IdDivision' => $c->id_division,
                    'IdSociedad' => $c->id_sociedad,
                    'IdViejo' => $c->id_viejo,
                    'Ocultar' => $c->ocultar,
                    'ComRecarga' => $c->com_recarga,
                    'GastoVtaTradicional' => $c->gasto_vta_tradicional,
                    'VariosLocales' => $c->varios_locales,
                    'AplicaParaPonderar' => $c->aplica_para_ponderar,
                    'ValorPonderar' => $c->valor_ponderar,
                    'CreadoPor' => $c->creado_por,
                    'FechaGrabado' => optional($c->fecha_grabado)->toDateTimeString(),
                    'ModificadoPor' => $c->modificado_por,
                    'FechaModificado' => optional($c->fecha_modificado)->toDateTimeString(),
                ];
            });

        return response()->json($centros);
    }

    public function updateCentrosCostoVisibilidad(Request $request)
    {
        $items = $request->input('items', []);

        if (! is_array($items)) {
            return response()->json([
                'message' => 'Formato invalido.',
            ], 422);
        }

        $actualizados = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = isset($item['id']) ? (int) $item['id'] : 0;
            if ($id <= 0) {
                continue;
            }

            $actualizados += CentroDeCosto::query()
                ->whereKey($id)
                ->update(['ocultar' => (bool) ($item['ocultar'] ?? false)]);
        }

        return response()->json([
            'message' => 'Configuracion guardada.',
            'actualizados' => $actualizados,
        ]);
    }

    public function syncCentrosCosto()
    {
        $result = $this->syncCentrosCostoFromExternal();

        if (! $result['ok']) {
            return response()->json([
                'message' => $result['message'],
                'status' => $result['status'] ?? null,
                'error' => $result['error'] ?? null,
            ], $result['status'] ?? 500);
        }

        return response()->json([
            'message' => 'Sincronización de centros de costo completada.',
            'total_recibidos' => $result['total_recibidos'],
            'creados' => $result['creados'],
            'actualizados' => $result['actualizados'],
            'omitidos' => $result['omitidos'],
        ]);
    }

    public function getEntradas(Request $request)
    {
        $cuenta = trim((string) $request->query('cuenta', ''));
        $fechaInicio = (string) $request->query('fecha_inicio', $request->query('fecha', date('Y-m-d')));
        $fechaFin = (string) $request->query('fecha_fin', $fechaInicio);

        if ($cuenta === '') {
            return response()->json([
                'message' => 'La cuenta es obligatoria.',
            ], 422);
        }

        try {
            $inicio = Carbon::createFromFormat('Y-m-d', $fechaInicio)->startOfDay();
            $fin = Carbon::createFromFormat('Y-m-d', $fechaFin)->startOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Formato de fechas inválido. Use YYYY-MM-DD.',
            ], 422);
        }

        if ($inicio->gt($fin)) {
            return response()->json([
                'message' => 'La fecha inicio no puede ser mayor que la fecha fin.',
            ], 422);
        }

        $syncResult = $this->syncDetalleCuentasFromExternal($cuenta, $inicio, $fin);

        if (!$syncResult['ok']) {
            return response()->json([
                'message' => $syncResult['message'],
            ], $syncResult['status']);
        }

        $detalles = DetalleCuenta::query()
            ->where('cuenta', $cuenta)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->orderBy('fecha')
            ->orderBy('no_asiento')
            ->get()
            ->map(function (DetalleCuenta $item) {
                return [
                    'NoAsiento' => $item->no_asiento,
                    'Fecha' => $item->fecha_raw,
                    'Ref' => $item->ref,
                    'NoRef' => $item->no_ref,
                    'Debito' => $item->debito,
                    'Credito' => $item->credito,
                    'Descripcion' => $item->descripcion,
                    'Grupo' => $item->grupo,
                    'SubGrupo' => $item->sub_grupo,
                    'Division' => $item->division,
                    'CentroCosto' => $item->centro_costo,
                    'Conciliado' => $item->conciliado,
                    'Modulo' => $item->modulo,
                    'FechaGrabado' => $item->fecha_grabado,
                    'FechaModificado' => $item->fecha_modificado,
                    'CreadoPor' => $item->creado_por,
                    'ModificadoPor' => $item->modificado_por,
                    'RefDesc' => $item->ref_desc,
                    'Sociedad' => $item->sociedad,
                ];
            })
            ->values();

        return response()->json([
            'result' => [
                'Det' => $detalles,
            ],
            'sync' => [
                'insertados' => $syncResult['insertados'],
                'actualizados' => $syncResult['actualizados'],
                'omitidos' => $syncResult['omitidos'],
            ],
        ]);
    }

    private function normalizeKeys($array)
    {
        $normalized = [];
        foreach ($array as $key => $value) {
            $upperKey = strtoupper($key);
            if (is_array($value)) {
                $normalized[$upperKey] = $this->normalizeKeys($value);
            } else {
                $normalized[$upperKey] = $value;
            }
        }
        return $normalized;
    }

    private function extractExternalCuentas(array $payload): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        if (isset($payload['RESULT']) && is_array($payload['RESULT'])) {
            if (isset($payload['RESULT']['DET']) && is_array($payload['RESULT']['DET'])) {
                return $payload['RESULT']['DET'];
            }

            if (array_is_list($payload['RESULT'])) {
                return $payload['RESULT'];
            }
        }

        if (isset($payload['DET']) && is_array($payload['DET'])) {
            return $payload['DET'];
        }

        return [];
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function syncCuentasFromExternal(): array
    {
        $url = 'https://apisj.azurewebsites.net/ApiSJ/CatalagoCta/Listar?strToken=87eb2d56-25f3-4d46-9cb0-73c07a550bd2&intIdEmpresa=168&strFiltros=[[%22AceptaMov%22%2C%20%221%22]]';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);

            return [
                'ok' => false,
                'message' => 'Error consultando API externa.',
                'error' => $error,
                'status' => 500,
            ];
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'ok' => false,
                'message' => 'La API externa respondió con error.',
                'status' => 502,
            ];
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return [
                'ok' => false,
                'message' => 'Respuesta inválida de API externa.',
                'status' => 502,
            ];
        }

        $decoded = $this->normalizeKeys($decoded);
        $items = $this->extractExternalCuentas($decoded);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                $skipped++;
                continue;
            }

            $item = $this->normalizeKeys($item);
            $cuenta = trim((string) ($item['CUENTA'] ?? ''));

            if ($cuenta === '') {
                $skipped++;
                continue;
            }

            $registro = CuentaContable::updateOrCreate(
                ['cuenta' => $cuenta],
                [
                    'descripcion' => (string) ($item['DESCRIPCION'] ?? ''),
                    'ctacontrol' => $this->nullableString($item['CTACONTROL'] ?? null),
                    'tipo' => $this->nullableString($item['TIPO'] ?? null),
                ]
            );

            if ($registro->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        return [
            'ok' => true,
            'total_recibidas' => count($items),
            'creadas' => $created,
            'actualizadas' => $updated,
            'omitidas' => $skipped,
        ];
    }

    private function syncCentrosCostoFromExternal(): array
    {
        $url = 'https://apisj.azurewebsites.net/fe/ApiSJ/api/ConsultaCentroCostos?strToken=87eb2d56-25f3-4d46-9cb0-73c07a550bd2&intIdEmpresa=168';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);

            return [
                'ok' => false,
                'message' => 'Error consultando API externa de centros de costo.',
                'error' => $error,
                'status' => 500,
            ];
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'ok' => false,
                'message' => 'La API externa respondió con error.',
                'status' => 502,
            ];
        }

        $decoded = json_decode($response, true);

        if (! is_array($decoded)) {
            return [
                'ok' => false,
                'message' => 'Respuesta inválida de API externa.',
                'status' => 502,
            ];
        }

        $items = $this->extractExternalCentrosCosto($decoded);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                $skipped++;
                continue;
            }

            $idCentroCosto = isset($item['IdCentroCosto']) ? (int) $item['IdCentroCosto'] : null;

            if (! $idCentroCosto) {
                $skipped++;
                continue;
            }

            $atributos = $this->extractAtributos($item);

            $registro = CentroDeCosto::updateOrCreate(
                ['id_centro_costo' => $idCentroCosto],
                [
                    'company_id' => $this->nullableString($item['CompanyID'] ?? null),
                    'descripcion' => (string) ($item['Descripcion'] ?? ''),
                    'cuenta' => $this->nullableString($item['Cuenta'] ?? null),
                    'inactivo' => (bool) ($item['Inactivo'] ?? false),
                    'id_grupo' => $this->nullableString($item['IdGrupo'] ?? null),
                    'id_sub_grupo' => $this->nullableString($item['IdSubGrupo'] ?? null),
                    'id_division' => $this->nullableString($item['IdDivision'] ?? null),
                    'id_sociedad' => $this->nullableString($item['IdSociedad'] ?? null),
                    'id_viejo' => $this->nullableString($item['IdViejo'] ?? null),
                    'id_centro_costo_resumir_en' => $this->nullableString($item['IdCentroCostoResumirEn'] ?? null),
                    'com_recarga' => (bool) ($item['ComRecarga'] ?? false),
                    'gasto_vta_tradicional' => (bool) ($item['GastoVtaTradicional'] ?? false),
                    'varios_locales' => (bool) ($item['VariosLocales'] ?? false),
                    'aplica_para_ponderar' => (bool) ($item['AplicaParaPonderar'] ?? false),
                    'valor_ponderar' => $this->parseDecimal($item['ValorPonderar'] ?? 0) ?? 0,
                    'creado_por' => $this->nullableString($item['CreadoPor'] ?? null),
                    'fecha_grabado' => $this->parseDateTime($item['FechaGrabado'] ?? null),
                    'modificado_por' => $this->nullableString($item['ModificadoPor'] ?? null),
                    'fecha_modificado' => $this->parseDateTime($item['FechaModificado'] ?? null),
                    'atributos' => empty($atributos) ? null : $atributos,
                ]
            );

            if ($registro->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        return [
            'ok' => true,
            'total_recibidos' => count($items),
            'creados' => $created,
            'actualizados' => $updated,
            'omitidos' => $skipped,
        ];
    }

    private function extractExternalCentrosCosto(array $payload): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['result', 'Result', 'RESULT', 'Det', 'DET'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                if (array_is_list($payload[$key])) {
                    return $payload[$key];
                }
                foreach (['Det', 'DET'] as $sub) {
                    if (isset($payload[$key][$sub]) && is_array($payload[$key][$sub])) {
                        return $payload[$key][$sub];
                    }
                }
            }
        }

        return [];
    }

    private function extractAtributos(array $item): array
    {
        $atributos = [];
        for ($i = 1; $i <= 73; $i++) {
            $key = 'Atr' . $i;
            if (array_key_exists($key, $item) && $item[$key] !== null && $item[$key] !== '') {
                $atributos[$key] = $item[$key];
            }
        }
        return $atributos;
    }

    private function parseDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function syncDetalleCuentasFromExternal(string $cuenta, Carbon $inicio, Carbon $fin): array
    {
        $insertados = 0;
        $actualizados = 0;
        $omitidos = 0;

        $fechaActual = $inicio->copy();

        while ($fechaActual->lte($fin)) {
            $items = $this->fetchDetalleByDate($cuenta, $fechaActual->toDateString());

            foreach ($items as $rawItem) {
                if (!is_array($rawItem)) {
                    $omitidos++;
                    continue;
                }

                $item = $this->normalizeKeys($rawItem);
                $fechaRaw = (string) ($item['FECHA'] ?? $fechaActual->toDateString());
                $fecha = $this->parseDate($fechaRaw) ?? $fechaActual->toDateString();

                $externalKey = sha1(implode('|', [
                    $cuenta,
                    (string) ($item['NOASIENTO'] ?? ''),
                    $fecha,
                    (string) ($item['REF'] ?? ''),
                    (string) ($item['NOREF'] ?? ''),
                    (string) ($item['MODULO'] ?? ''),
                    (string) ($item['DEBITO'] ?? ''),
                    (string) ($item['CREDITO'] ?? ''),
                ]));

                $detalle = DetalleCuenta::updateOrCreate(
                    ['external_key' => $externalKey],
                    [
                        'cuenta' => $cuenta,
                        'no_asiento' => $this->nullableString($item['NOASIENTO'] ?? null),
                        'fecha' => $fecha,
                        'fecha_raw' => $fechaRaw,
                        'ref' => $this->nullableString($item['REF'] ?? null),
                        'no_ref' => $this->nullableString($item['NOREF'] ?? null),
                        'debito' => $this->parseDecimal($item['DEBITO'] ?? null),
                        'credito' => $this->parseDecimal($item['CREDITO'] ?? null),
                        'descripcion' => $this->nullableString($item['DESCRIPCION'] ?? null),
                        'grupo' => $this->nullableString($item['GRUPO'] ?? null),
                        'sub_grupo' => $this->nullableString($item['SUBGRUPO'] ?? null),
                        'division' => $this->nullableString($item['DIVISION'] ?? null),
                        'centro_costo' => $this->nullableString($item['CENTROCOSTO'] ?? null),
                        'conciliado' => $this->nullableString($item['CONCILIADO'] ?? null),
                        'modulo' => $this->nullableString($item['MODULO'] ?? null),
                        'fecha_grabado' => $this->nullableString($item['FECHAGRABADO'] ?? null),
                        'fecha_modificado' => $this->nullableString($item['FECHAMODIFICADO'] ?? null),
                        'creado_por' => $this->nullableString($item['CREADOPOR'] ?? null),
                        'modificado_por' => $this->nullableString($item['MODIFICADOPOR'] ?? null),
                        'ref_desc' => $this->nullableString($item['REFDESC'] ?? null),
                        'sociedad' => $this->nullableString($item['SOCIEDAD'] ?? null),
                    ]
                );

                if ($detalle->wasRecentlyCreated) {
                    $insertados++;
                } else {
                    $actualizados++;
                }
            }

            $fechaActual->addDay();
        }

        return [
            'ok' => true,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'omitidos' => $omitidos,
            'status' => 200,
        ];
    }

    private function fetchDetalleByDate(string $cuenta, string $fecha): array
    {
        $url = 'https://apisj.azurewebsites.net/ApiSJ/EntradaDiario/Listar?strToken=87eb2d56-25f3-4d46-9cb0-73c07a550bd2&intIdEmpresa=168&dtFecha=' . $fecha . '&strCuenta=' . urlencode($cuenta);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            curl_close($curl);
            return [];
        }

        curl_close($curl);

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return [];
        }

        $decoded = $this->normalizeKeys($decoded);

        if (isset($decoded['RESULT']) && is_array($decoded['RESULT']) && isset($decoded['RESULT']['DET']) && is_array($decoded['RESULT']['DET'])) {
            return $decoded['RESULT']['DET'];
        }

        if (isset($decoded['DET']) && is_array($decoded['DET'])) {
            return $decoded['DET'];
        }

        return [];
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $clean = str_replace(',', '', trim((string) $value));

        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }
}
