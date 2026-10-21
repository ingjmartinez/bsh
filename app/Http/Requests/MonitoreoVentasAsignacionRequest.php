<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonitoreoVentasAsignacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('servicios_generales.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'central' => ['required', 'string', 'max:100', 'exists:agencias,central'],
            'grupo' => ['required', 'string', 'max:100', 'exists:agencias,grupo'],
            'asesor_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return ['central.exists' => 'La central no existe.', 'grupo.exists' => 'El grupo no existe.', 'asesor_id.exists' => 'El asesor no existe.'];
    }
}
