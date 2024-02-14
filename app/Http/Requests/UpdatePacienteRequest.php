<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePacienteRequest extends FormRequest
{
    const MAX_LENGTH = 'max:64';
    const NAME_REGEX = 'regex:/^[A-Za-z]+\s?[A-Za-z]+$/';
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

        $method = $this->method();

        if ($method == 'PUT') {
            return [
                'nombre' => ['required', 'string', self::MAX_LENGTH, self::NAME_REGEX],
                'apellido' => ['required', 'string', self::MAX_LENGTH, self::NAME_REGEX],
                'fecha_de_nacimiento' => ['required', 'date'],
                'password' => ['required', 'string'], // Asegúrate de ajustar las reglas según tus necesidades
                'correo' => ['required', 'email', Rule::unique('pacientes', 'correo')
                    ->ignore($this->paciente->id)],
                'direccion' => ['nullable', 'string'],
                'telefono' => ['required', 'string', Rule::unique('pacientes', 'telefono')
                    ->ignore($this->paciente->id)],
                'contacto_opcional' => ['nullable', 'string'],
                'activo' => ['required', 'integer', 'in:0,1'], // Se espera que 'activo' sea 0 o 1
            ];
        } else {
            return [
                'nombre' => ['sometimes', 'required', 'string', self::MAX_LENGTH, self::NAME_REGEX],
                'apellido' => ['sometimes', 'required', 'string', self::MAX_LENGTH, self::NAME_REGEX],
                'fecha_de_nacimiento' => ['sometimes', 'required', 'date'],
                'password' => ['sometimes', 'required', 'string'],
                'correo' => ['sometimes', 'required', 'email', Rule::unique('pacientes', 'correo')
                    ->ignore($this->paciente->id)],
                'direccion' => ['sometimes', 'nullable', 'string'],
                'telefono' => ['sometimes', 'required', 'string', Rule::unique('pacientes', 'telefono')
                    ->ignore($this->paciente->id)],
                'contacto_opcional' => ['sometimes', 'nullable', 'string'],
                'activo' => ['sometimes', 'required', 'integer', 'in:0,1'], // Se espera que 'activo' sea 0 o 1
            ];
        }
    }
}
