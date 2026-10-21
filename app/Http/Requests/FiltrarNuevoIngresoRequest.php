<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiltrarNuevoIngresoRequest extends FormRequest
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
            'grupo' => ['nullable', 'string', 'max:191'],
            'central' => ['nullable', 'string', 'max:191'],
            'gerente' => ['nullable', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'grupo.max' => 'El grupo seleccionado no es válido.',
            'central.max' => 'La central seleccionada no es válida.',
            'gerente.max' => 'El gerente seleccionado no es válido.',
        ];
    }
}
