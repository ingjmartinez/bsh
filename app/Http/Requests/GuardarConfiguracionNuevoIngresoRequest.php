<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarConfiguracionNuevoIngresoRequest extends FormRequest
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
            'dias_habiles' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'dias_habiles.required' => 'Indique el límite de días hábiles.',
            'dias_habiles.integer' => 'El límite de días hábiles debe ser un número entero.',
            'dias_habiles.min' => 'El límite mínimo permitido es 1 día hábil.',
            'dias_habiles.max' => 'El límite máximo permitido es 20 días hábiles.',
        ];
    }
}
