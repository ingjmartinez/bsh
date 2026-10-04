<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PremiosPagadosProductoRequest extends FormRequest
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
            'fecha_inicio' => ['nullable', 'required_with:fecha_fin', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'required_with:fecha_inicio', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
            'grupo' => ['nullable', 'string', 'max:100', 'exists:agencias,grupo'],
            'vista' => ['nullable', 'in:consolidado,terminal'],
            'categoria' => ['nullable', 'in:todos,tradicional,no_tradicional'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required_with' => 'Seleccione la fecha de inicio.',
            'fecha_fin.required_with' => 'Seleccione la fecha final.',
            'fecha_inicio.date_format' => 'La fecha de inicio debe tener el formato año-mes-día.',
            'fecha_fin.date_format' => 'La fecha final debe tener el formato año-mes-día.',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha de inicio.',
            'grupo.exists' => 'Seleccione un grupo válido.',
            'vista.in' => 'Seleccione una vista válida.',
            'categoria.in' => 'Seleccione una categoría válida.',
        ];
    }
}
