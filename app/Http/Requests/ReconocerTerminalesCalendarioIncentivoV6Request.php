<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class ReconocerTerminalesCalendarioIncentivoV6Request extends FormRequest
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
            'file' => ['nullable', File::types(['xlsx', 'xls', 'csv'])->max(4096)],
            'terminales_manual' => ['nullable', 'string', 'max:100000'],
            'sistema' => ['nullable', Rule::in(['Todos', 'Lotobet', 'Lotonet'])],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->hasFile('file') && trim($this->string('terminales_manual')->toString()) === '') {
                    $validator->errors()->add('terminales_manual', 'Selecciona un archivo o escribe al menos una terminal.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.mimes' => 'El archivo debe ser XLSX, XLS o CSV.',
            'file.max' => 'El archivo no puede superar los 4 MB.',
            'sistema.in' => 'El sistema debe ser Todos, Lotobet o Lotonet.',
        ];
    }
}
