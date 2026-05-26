<?php

namespace App\Http\Controllers;

use App\Models\CentroDeCosto;
use App\Models\CuentaContable;
use App\Models\DetalleCuenta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class Api extends Controller
{
    private const CONTABILIDAD_TOKEN = '78177a3a-3679-4899-bf9f-22d3badeb737';
    private const CONTABILIDAD_EMPRESAS = ['126', '100'];

    public function getCuentas(Request $request)
    {
        $empresa = $this->empresaContabilidadFromRequest($request);

        if ($this->cuentasQueryByEmpresa($empresa)->count() === 0) {
            $this->syncCuentasFromExternal($empresa);
        }

        $cuentas = $this->cuentasQueryByEmpresa($empresa)
            ->orderBy('company_id')
            ->orderBy('cuenta')
            ->get()
            ->map(function (CuentaContable $cuenta) {
                return [
                    'id' => $cuenta->id,
                    'CompanyID' => $cuenta->company_id,
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
            'company_id' => ['required', Rule::in(self::CONTABILIDAD_EMPRESAS)],
            'cuenta' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cuentas_contables', 'cuenta')->where('company_id', $request->input('company_id')),
            ],
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
            'company_id' => ['required', Rule::in(self::CONTABILIDAD_EMPRESAS)],
            'cuenta' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cuentas_contables', 'cuenta')
                    ->where('company_id', $request->input('company_id'))
                    ->ignore($cuenta->id),
            ],
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

    public function syncCuentas(Request $request)
    {
        $empresa = $this->empresaContabilidadFromRequest($request);
        $syncResult = $this->syncCuentasFromExternal($empresa);

        if (!$syncResult['ok']) {
            return response()->json([
                'message' => $syncResult['message'],
                'status' => $syncResult['status'] ?? null,
                'error' => $syncResult['error'] ?? null,
            ], $syncResult['status'] ?? 500);
        }

        return response()->json([
            'message' => 'Sincronización completada correctamente.',
            'empresas' => $syncResult['empresas'],
            'total_recibidas' => $syncResult['total_recibidas'],
            'creadas' => $syncResult['creadas'],
            'actualizadas' => $syncResult['actualizadas'],
            'omitidas' => $syncResult['omitidas'],
        ]);
    }


    public function getCentrosCosto(Request $request)
    {
        $empresa = trim((string) $request->query('empresa', ''));

        $query = CentroDeCosto::query();

        if (in_array($empresa, ['126', '100'], true)) {
            $query->where(function ($q) use ($empresa) {
                $q->where('company_id', $empresa)
                    ->orWhere('company_id', 'like', $empresa . '-%');
            });
        }

        $centros = $query
            ->orderBy('id_centro_costo')
            ->get()
            ->map(function (CentroDeCosto $c) {
                return [
                    'id' => $c->id,
                    'IdCentroCosto' => $c->id_centro_costo,
                    'CompanyID' => $this->normalizeCentroCostoCompanyId($c->company_id),
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

    public function syncCentrosCosto(Request $request)
    {
        @set_time_limit(240);
        ini_set('max_execution_time', '240');
        ini_set('memory_limit', '512M');

        $empresa = trim((string) $request->input('empresa', ''));

        if (! in_array($empresa, ['126', '100'], true)) {
            return response()->json([
                'message' => 'Debe seleccionar una empresa valida para sincronizar.',
            ], 422);
        }

        try {
            $result = $this->syncCentrosCostoFromExternal($empresa);
        } catch (\Throwable $e) {
            Log::error('Error sincronizando centros de costo', [
                'empresa' => $empresa,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error interno sincronizando centros de costo.',
                'error' => $e->getMessage(),
            ], 500);
        }

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
            'empresa' => $result['empresa'],
        ]);
    }

    public function getEntradas(Request $request)
    {
        $cuenta = trim((string) $request->query('cuenta', ''));
        $empresa = $this->empresaContabilidadFromRequest($request);
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

        $syncResult = $this->syncDetalleCuentasFromExternal($cuenta, $inicio, $fin, $empresa);

        if (!$syncResult['ok']) {
            return response()->json([
                'message' => $syncResult['message'],
            ], $syncResult['status']);
        }

        $detalles = DetalleCuenta::query()
            ->where('cuenta', $cuenta)
            ->when(
                $empresa === 'todos',
                fn ($query) => $query->whereIn('company_id', self::CONTABILIDAD_EMPRESAS),
                fn ($query) => $query->where('company_id', $empresa)
            )
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->orderBy('company_id')
            ->orderBy('fecha')
            ->orderBy('no_asiento')
            ->get()
            ->map(function (DetalleCuenta $item) {
                return [
                    'NoAsiento' => $item->no_asiento,
                    'CompanyID' => $item->company_id,
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
                'empresas' => $syncResult['empresas'] ?? [],
            ],
        ]);
    }

    private function empresaContabilidadFromRequest(Request $request): string
    {
        $empresa = trim((string) $request->input('empresa', '126'));

        if ($empresa === 'todos') {
            return 'todos';
        }

        return in_array($empresa, self::CONTABILIDAD_EMPRESAS, true) ? $empresa : '126';
    }

    private function empresasContabilidad(string $empresa): array
    {
        return $empresa === 'todos' ? self::CONTABILIDAD_EMPRESAS : [$empresa];
    }

    private function cuentasQueryByEmpresa(string $empresa)
    {
        $query = CuentaContable::query();

        if ($empresa === 'todos') {
            return $query->whereIn('company_id', self::CONTABILIDAD_EMPRESAS);
        }

        return $query->where('company_id', $empresa);
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

    private function externalValue(array $item, string $key, $default = null)
    {
        if (array_key_exists($key, $item)) {
            return $item[$key];
        }

        $upperKey = strtoupper($key);
        if (array_key_exists($upperKey, $item)) {
            return $item[$upperKey];
        }

        return $default;
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'si', 'sí', 'yes', 'on'], true);
    }

    private function normalizeCentroCostoCompanyId($value, string $fallback = ''): string
    {
        $companyId = trim((string) ($value ?? ''));

        if (substr($companyId, 0, 3) === '126') {
            return '126';
        }

        if (substr($companyId, 0, 3) === '100') {
            return '100';
        }

        return in_array($fallback, self::CONTABILIDAD_EMPRESAS, true) ? $fallback : $companyId;
    }

    private function syncCuentasFromExternal(string $empresa): array
    {
        $empresas = $this->empresasContabilidad($empresa);
        $total = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($empresas as $empresaActual) {
            $result = $this->fetchCuentasByEmpresa($empresaActual);

            if (! $result['ok']) {
                return $result;
            }

            $items = $result['items'];
            $total += count($items);

            foreach ($items as $item) {
                if (! is_array($item)) {
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
                    ['company_id' => $empresaActual, 'cuenta' => $cuenta],
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
        }

        return [
            'ok' => true,
            'empresas' => $empresas,
            'total_recibidas' => $total,
            'creadas' => $created,
            'actualizadas' => $updated,
            'omitidas' => $skipped,
        ];
    }

    private function fetchCuentasByEmpresa(string $empresa): array
    {
        $url = 'https://apisj.azurewebsites.net/ApiSJ/CatalagoCta/Listar?strToken=' . urlencode(self::CONTABILIDAD_TOKEN)
            . '&intIdEmpresa=' . urlencode($empresa);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Accept: application/text',
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
                'message' => 'La API externa respondió con error para la empresa ' . $empresa . '.',
                'status' => 502,
            ];
        }

        $decoded = json_decode($response, true);

        if (! is_array($decoded)) {
            return [
                'ok' => false,
                'message' => 'Respuesta inválida de API externa para la empresa ' . $empresa . '.',
                'status' => 502,
            ];
        }

        return [
            'ok' => true,
            'items' => $this->extractExternalCuentas($this->normalizeKeys($decoded)),
        ];
    }

    private function syncCentrosCostoFromExternal(string $empresa): array
    {
        $token = '78177a3a-3679-4899-bf9f-22d3badeb737';
        $url = 'https://apisj.azurewebsites.net/fe/ApiSJ/api/ConsultaCentroCostos?strToken=' . urlencode($token) . '&intIdEmpresa=' . urlencode($empresa);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
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
            curl_close($curl);

            return [
                'ok' => false,
                'message' => 'Error consultando API externa de centros de costo.',
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

            $item = $this->normalizeKeys($item);
            $idCentroCosto = (int) ($this->externalValue($item, 'IdCentroCosto', 0) ?? 0);

            if (! $idCentroCosto) {
                $skipped++;
                continue;
            }

            $atributos = $this->extractAtributos($item);

            $registro = CentroDeCosto::updateOrCreate(
                ['id_centro_costo' => $idCentroCosto],
                [
                    'company_id' => $empresa,
                    'descripcion' => (string) ($this->externalValue($item, 'Descripcion', '') ?? ''),
                    'cuenta' => $this->nullableString($this->externalValue($item, 'Cuenta')),
                    'inactivo' => $this->parseBoolean($this->externalValue($item, 'Inactivo', false)),
                    'id_grupo' => $this->nullableString($this->externalValue($item, 'IdGrupo')),
                    'id_sub_grupo' => $this->nullableString($this->externalValue($item, 'IdSubGrupo')),
                    'id_division' => $this->nullableString($this->externalValue($item, 'IdDivision')),
                    'id_sociedad' => $this->nullableString($this->externalValue($item, 'IdSociedad')),
                    'id_viejo' => $this->nullableString($this->externalValue($item, 'IdViejo')),
                    'id_centro_costo_resumir_en' => $this->nullableString($this->externalValue($item, 'IdCentroCostoResumirEn')),
                    'com_recarga' => $this->parseBoolean($this->externalValue($item, 'ComRecarga', false)),
                    'gasto_vta_tradicional' => $this->parseBoolean($this->externalValue($item, 'GastoVtaTradicional', false)),
                    'varios_locales' => $this->parseBoolean($this->externalValue($item, 'VariosLocales', false)),
                    'aplica_para_ponderar' => $this->parseBoolean($this->externalValue($item, 'AplicaParaPonderar', false)),
                    'valor_ponderar' => $this->parseDecimal($this->externalValue($item, 'ValorPonderar', 0)) ?? 0,
                    'creado_por' => $this->nullableString($this->externalValue($item, 'CreadoPor')),
                    'fecha_grabado' => $this->parseDateTime($this->externalValue($item, 'FechaGrabado')),
                    'modificado_por' => $this->nullableString($this->externalValue($item, 'ModificadoPor')),
                    'fecha_modificado' => $this->parseDateTime($this->externalValue($item, 'FechaModificado')),
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
            'empresa' => $empresa,
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
            $value = $this->externalValue($item, $key);
            if ($value !== null && $value !== '') {
                $atributos[$key] = $value;
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

    private function syncDetalleCuentasFromExternal(string $cuenta, Carbon $inicio, Carbon $fin, string $empresa): array
    {
        $insertados = 0;
        $actualizados = 0;
        $omitidos = 0;
        $empresas = $this->empresasContabilidad($empresa);

        foreach ($empresas as $empresaActual) {
            $fechaActual = $inicio->copy();

            while ($fechaActual->lte($fin)) {
                $items = $this->fetchDetalleByDate($cuenta, $fechaActual->toDateString(), $empresaActual);

                foreach ($items as $rawItem) {
                    if (!is_array($rawItem)) {
                        $omitidos++;
                        continue;
                    }

                    $item = $this->normalizeKeys($rawItem);
                    $fechaRaw = (string) ($item['FECHA'] ?? $fechaActual->toDateString());
                    $fecha = $this->parseDate($fechaRaw) ?? $fechaActual->toDateString();

                    $externalKey = sha1(implode('|', [
                        $empresaActual,
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
                            'company_id' => $empresaActual,
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
        }

        return [
            'ok' => true,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'omitidos' => $omitidos,
            'empresas' => $empresas,
            'status' => 200,
        ];
    }

    private function fetchDetalleByDate(string $cuenta, string $fecha, string $empresa): array
    {
        $url = 'https://apisj.azurewebsites.net/ApiSJ/EntradaDiario/Listar?strToken=' . urlencode(self::CONTABILIDAD_TOKEN)
            . '&intIdEmpresa=' . urlencode($empresa)
            . '&dtFecha=' . urlencode($fecha)
            . '&strCuenta=' . urlencode($cuenta);

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
