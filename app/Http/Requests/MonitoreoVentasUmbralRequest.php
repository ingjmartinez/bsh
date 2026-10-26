<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonitoreoVentasUmbralRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('servicios_generales.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asesor_id' => ['required', 'integer', 'exists:users,id'],
            'umbrales' => ['required', 'array'],
            'umbrales.tradicional' => ['required', 'numeric', 'min:0'],
            'umbrales.no_tradicional' => ['required', 'numeric', 'min:0'],
            'umbrales.recargas' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'asesor_id.required' => 'Seleccione un asesor.',
            'umbrales.*.required' => 'Indique el umbral de cada producto.',
            'umbrales.*.min' => 'Los umbrales no pueden ser negativos.',
        ];
    }
}
