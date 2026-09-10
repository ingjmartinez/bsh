<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VentasDsVirtualDateRequest extends FormRequest
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
            'fecha' => ['required', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha.required' => 'Debe seleccionar una fecha.',
            'fecha.date_format' => 'La fecha debe tener el formato YYYY-MM-DD.',
        ];
    }
}
