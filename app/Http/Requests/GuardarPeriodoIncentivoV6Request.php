<?php

namespace App\Http\Requests;

use App\Models\IncentivoTerminalTipoPago;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarPeriodoIncentivoV6Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
            'sistema' => ['required', Rule::in(['Todos'])],
            'alcance_productos' => ['required', Rule::in(['completo'])],
            'modo_calculo' => ['required', Rule::in(['general', 'separado_empresa'])],
            'tipo_pago_defecto' => ['required', Rule::in(IncentivoTerminalTipoPago::TIPOS_PAGO)],
            'min_dias_venta' => ['required', 'integer', 'min:1', 'max:366'],
            'rangos_pago_por_tipo' => ['nullable', 'array'],
            'terminales_excluidas' => ['nullable', 'array'],
            'terminales_excluidas.*' => ['string', 'max:50', 'distinct'],
            'faltantes_aplicados' => ['required', 'accepted'],
            'desvinculados_aplicados' => ['required', 'accepted'],
            'detalles' => ['required', 'array', 'min:1', 'max:20000'],
            'detalles.*.cedula' => ['required', 'string', 'max:30'],
            'detalles.*.empleadoid' => ['nullable', 'string', 'max:50'],
            'detalles.*.nombre' => ['required', 'string', 'max:200'],
            'detalles.*.empresa' => ['required', 'string', 'max:100'],
            'detalles.*.ultima_terminal' => ['nullable', 'string', 'max:50'],
            'detalles.*.ultima_agencia_nombre' => ['nullable', 'string', 'max:200'],
            'detalles.*.ventas_ultimo_mes' => ['required', 'numeric', 'min:0'],
            'detalles.*.ventas_mes_actual' => ['required', 'numeric', 'min:0'],
            'detalles.*.dias_ventas' => ['required', 'integer', 'min:0', 'max:366'],
            'detalles.*.horas_total' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.incentivo_generado' => ['required', 'numeric', 'min:0'],
            'detalles.*.monto_pagado' => ['required', 'numeric', 'min:0'],
            'detalles.*.motivos' => ['nullable', 'array'],
            'detalles.*.motivos.*' => [Rule::in(['faltante', 'desvinculado', 'agencia_excluida', 'meta_no_alcanzada'])],
            'detalles.*.tipos_pago_detalle' => ['nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['fecha_inicio', 'fecha_fin'])) {
                    return;
                }

                $fechaInicio = $this->date('fecha_inicio');
                $fechaFin = $this->date('fecha_fin');

                if ($fechaInicio && $fechaFin && ! $fechaInicio->isSameMonth($fechaFin)) {
                    $validator->errors()->add('fecha_fin', 'El período guardado debe pertenecer a un solo mes.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'sistema.in' => 'El cierre mensual debe generarse con el sistema Todos.',
            'alcance_productos.in' => 'El cierre mensual solo puede guardarse desde Reporte completo.',
            'faltantes_aplicados.accepted' => 'Debes consultar y aplicar los faltantes antes de guardar el período.',
            'desvinculados_aplicados.accepted' => 'Debes consultar y aplicar los desvinculados antes de guardar el período.',
        ];
    }
}
