<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarMovimientoUsuarioRequest extends FormRequest
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
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'terminal_origen' => ['nullable', 'string', 'max:50', 'required_without_all:centro_costo_origen,grupo_origen'],
            'centro_costo_origen' => ['nullable', 'string', 'max:50', 'required_without_all:terminal_origen,grupo_origen'],
            'grupo_origen' => ['nullable', 'string', 'max:100', 'required_without_all:terminal_origen,centro_costo_origen'],
            'terminal_destino' => ['nullable', 'string', 'max:50', 'required_without_all:centro_costo_destino,grupo_destino'],
            'centro_costo_destino' => ['nullable', 'string', 'max:50', 'required_without_all:terminal_destino,grupo_destino'],
            'grupo_destino' => ['nullable', 'string', 'max:100', 'required_without_all:terminal_destino,centro_costo_destino'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
