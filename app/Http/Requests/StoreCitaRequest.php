<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
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
            'estado' => ['nullabe', 'string'],
            'fecha_inicio_cita' => ['required', 'date'],
            'fecha_fin_cita' => ['nullable', 'date'],
            'motivo' => ['required', 'string'],
            'paciente_id' => ['required', 'integer', Rule::exists('pacientes', 'id')],
            'empleado_id' => ['required', 'integer', Rule::exists('empleados', 'id')],
        ];
    }
}
