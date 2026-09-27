<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BiLotobetProductosRequest extends FormRequest
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
            'fecha_desde' => ['required', 'date_format:Y-m-d'],
            'fecha_hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_desde'],
            'categoria' => ['required', 'in:tradicional,no_tradicional'],
            'grupo' => ['nullable', 'string', 'max:191'],
            'central' => ['nullable', 'string', 'max:191'],
            'gerente' => ['nullable', 'string', 'max:191'],
            'terminal' => ['nullable', 'string', 'max:191'],
        ];
    }
}
