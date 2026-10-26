<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListarDivisionesCentroCostoRequest extends FormRequest
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
            'empresa' => ['required', Rule::in(['100', '126'])],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa.required' => 'Selecciona una empresa para consultar sus divisiones.',
            'empresa.in' => 'La empresa seleccionada no es válida.',
        ];
    }
}
