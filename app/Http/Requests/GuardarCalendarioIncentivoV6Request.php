<?php

namespace App\Http\Requests;

use App\Models\IncentivoTerminalTipoPago;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarCalendarioIncentivoV6Request extends FormRequest
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
            'asignaciones' => ['required', 'array', 'min:1', 'max:10000'],
            'asignaciones.*.sistema' => ['required', Rule::in(IncentivoTerminalTipoPago::SISTEMAS)],
            'asignaciones.*.terminal' => ['required', 'string', 'max:50'],
            'asignaciones.*.fecha' => ['required', 'date_format:Y-m-d'],
            'asignaciones.*.tipo_pago' => ['nullable', Rule::in(IncentivoTerminalTipoPago::TIPOS_PAGO)],
        ];
    }

    public function messages(): array
    {
        return [
            'asignaciones.required' => 'Debes seleccionar al menos una terminal y una fecha.',
            'asignaciones.max' => 'Solo puedes guardar hasta 10,000 celdas por operación.',
            'asignaciones.*.sistema.in' => 'El sistema debe ser Lotobet o Lotonet.',
            'asignaciones.*.terminal.required' => 'Cada asignación debe indicar una terminal.',
            'asignaciones.*.fecha.date_format' => 'Cada fecha debe tener el formato YYYY-MM-DD.',
            'asignaciones.*.tipo_pago.in' => 'El tipo de pago debe ser 60, 70 u 80.',
        ];
    }
}
