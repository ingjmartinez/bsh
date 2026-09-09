<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultarCalendarioIncentivoV6Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha_ini' => ['required', 'date_format:Y-m-d'],
            'fecha_fin' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:fecha_ini',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $fechaInicio = Carbon::createFromFormat('Y-m-d', (string) $this->input('fecha_ini'));
                    $fechaFin = Carbon::createFromFormat('Y-m-d', (string) $value);

                    if ($fechaInicio->diffInDays($fechaFin) > 31) {
                        $fail('El calendario permite consultar un máximo de 32 días.');
                    }
                },
            ],
            'sistema' => ['nullable', Rule::in(['Todos', 'Lotobet', 'Lotonet'])],
            'buscar' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([25, 50, 100])],
        ];
    }
}
