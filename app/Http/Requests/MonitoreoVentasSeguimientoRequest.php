<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonitoreoVentasSeguimientoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $caso = $this->route('caso');

        return $caso && (int) $caso->asesor_id === (int) $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'causa' => ['required', 'string', 'max:5000'],
            'gestion' => ['required', 'string', 'max:5000'],
            'resultado' => ['required', 'string', 'max:5000'],
            'cerrar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['causa.required' => 'Indica la causa.', 'gestion.required' => 'Describe la gestión realizada.', 'resultado.required' => 'Registra el resultado.'];
    }
}
