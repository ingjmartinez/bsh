<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SincronizarEmpleadosRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'empresa' => ['required', Rule::in(['100', '126'])],
            'limite' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'empresa.required' => 'Debe seleccionar una empresa.',
            'empresa.in' => 'La empresa debe ser 126 o 100.',
            'limite.required' => 'Debe indicar cuántos registros desea consultar.',
            'limite.integer' => 'La cantidad de registros debe ser un número entero.',
            'limite.min' => 'Debe consultar al menos un registro.',
            'limite.max' => 'Puede consultar un máximo de 10,000 registros.',
        ];
    }
}
