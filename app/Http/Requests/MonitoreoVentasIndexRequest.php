<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonitoreoVentasIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('servicios_generales.view') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'estado' => ['nullable', 'in:pendiente,en_seguimiento,cerrado'],
        ];
    }

    public function messages(): array
    {
        return ['fecha.date_format' => 'La fecha debe tener formato YYYY-MM-DD.', 'estado.in' => 'El estado seleccionado no es válido.'];
    }
}
