<?php

namespace App\Http\Requests;

use App\Models\IncentivoTerminalTipoPago;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReporteNuevoIncentivoV6Request extends FormRequest
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
            'fecha_ini' => ['required', 'date_format:Y-m-d'],
            'fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_ini'],
            'sistema' => ['nullable', Rule::in(['Todos', 'Lotobet', 'Lotonet'])],
            'min_dias_venta' => ['nullable', 'integer', 'min:1'],
            'filtro_cumplimiento' => ['nullable', Rule::in(['todos', 'cumplidos', 'no_cumplidos'])],
            'tipo_pago' => ['nullable', Rule::in(IncentivoTerminalTipoPago::TIPOS_PAGO)],
            'rangos_pago' => ['nullable', 'json'],
            'rangos_pago_por_tipo' => ['nullable', 'json'],
            'modo_calculo' => ['nullable', Rule::in(['general', 'separado_empresa'])],
            'alcance_productos' => ['nullable', Rule::in(['completo', 'no_tradicionales'])],
            'terminales_excluidas' => ['nullable', 'json'],
        ];
    }
}
