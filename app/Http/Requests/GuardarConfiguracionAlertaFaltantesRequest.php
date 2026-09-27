<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarConfiguracionAlertaFaltantesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'minimo_faltantes' => ['required', 'integer', 'min:1', 'max:1000'],
            'maximo_monto' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'minimo_faltantes.required' => 'Indique el mínimo de faltantes.',
            'minimo_faltantes.integer' => 'El mínimo de faltantes debe ser un número entero.',
            'minimo_faltantes.min' => 'El mínimo de faltantes debe ser al menos 1.',
            'maximo_monto.required' => 'Indique el monto máximo.',
            'maximo_monto.numeric' => 'El monto máximo debe ser numérico.',
            'maximo_monto.min' => 'El monto máximo no puede ser negativo.',
        ];
    }
}
