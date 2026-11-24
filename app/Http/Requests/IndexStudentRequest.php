<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filtros del listado de alumnos. Todos los parámetros son opcionales
 * y se validan antes de llegar al Service.
 */
class IndexStudentRequest extends FormRequest
{
    /**
     * Cualquier visitante puede consultar el listado en esta fase (auth en F3).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(['active', 'retired'])],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'group_type_id' => ['nullable', 'integer', 'exists:group_types,id'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
