<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta de un alumno: datos personales, grupo y tarifa.
 * El grupo debe pertenecer a la sucursal elegida y estar activo.
 */
class StoreStudentRequest extends FormRequest
{
    /**
     * Cualquier visitante puede dar de alta en esta fase (auth en F3).
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
            'name' => ['required', 'string', 'max:120'],
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
            'group_id' => [
                'required',
                'integer',
                Rule::exists('groups', 'id')->where(function ($query): void {
                    $query->where('branch_id', $this->input('branch_id'))
                        ->where('is_active', true);
                }),
            ],
            'fee' => ['sometimes', 'numeric', 'min:0', 'max:999'],
            'payment_day' => ['required', 'integer', 'between:1,28'],
            'is_promo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Mensajes en español (el proyecto no publica archivos de idioma).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del bailarín es obligatorio.',
            'name.max' => 'El nombre no puede superar los 120 caracteres.',
            'branch_id.required' => 'Selecciona una sucursal.',
            'branch_id.exists' => 'La sucursal seleccionada no existe.',
            'group_id.required' => 'Selecciona un grupo.',
            'group_id.exists' => 'El grupo no pertenece a la sucursal seleccionada o no está activo.',
            'fee.numeric' => 'La mensualidad debe ser un número.',
            'fee.min' => 'La mensualidad no puede ser negativa.',
            'fee.max' => 'La mensualidad es demasiado alta.',
            'payment_day.required' => 'Ingresa el día fijo de pago.',
            'payment_day.integer' => 'El día de pago debe ser un número entero.',
            'payment_day.between' => 'El día de pago debe estar entre 1 y 28.',
            'is_promo.boolean' => 'La tarifa promocional no es válida.',
        ];
    }
}
