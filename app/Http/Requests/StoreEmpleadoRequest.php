<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmpleadoRequest extends FormRequest
{
    // Define constantes para los literales utilizados en las reglas de validación
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
        return [
            'grupo_id' => ['required', 'integer', Rule::exists('grupos', 'id')],
            'nombre' => ['required', 'string', self::MAX_LENGTH, self::NAME_REGEX],
            'apellido' => ['required', 'string', self::MAX_LENGTH, self::NAME_REGEX],
            'edad' => ['required', 'integer'],
            'password' => ['required', 'string'], // Asegúrate de ajustar las reglas según tus necesidades
            'correo' => ['required', 'email', 'unique:empleados'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['required', 'string', 'unique:empleados'],
            'fecha_de_contratacion' => ['required', 'date'],
            'contacto_opcional' => ['nullable', 'string'],
            'activo' => ['required', 'integer', 'in:0,1'], // Se espera que 'activo' sea 0 o 1
        ];
    }

    public function messages()
    {
        return [
            // Se colocan los mensajes sin tilde para que se lean bien en consola
            'grupo_id.required' => 'El grupo es obligatorio.',
            'grupo_id.integer' => 'El grupo debe ser un numero entero.',
            'grupo_id.exists' => 'El grupo seleccionado no existe.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener mas de 64 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y un espacio entre ellas.',

            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.string' => 'El apellido debe ser una cadena de texto.',
            'apellido.max' => 'El apellido no puede tener mas de 64 caracteres.',
            'apellido.regex' => 'El apellido solo puede contener letras y un espacio entre ellas.',

            'edad.required' => 'La edad es obligatoria.',
            'edad.integer' => 'La edad debe ser un numero entero.',

            'password.required' => 'La password es obligatoria.',
            'password.string' => 'La password debe ser una cadena de texto.',

            'correo.required' => 'El correo electronico es obligatorio.',
            'correo.email' => 'El correo electronico debe ser una direccion de correo valida.',
            'correo.unique' => 'Este correo electronico ya esta registrado.',

            'direccion.string' => 'La direccion debe ser una cadena de texto.',

            'telefono.required' => 'El telefono es obligatorio.',
            'telefono.string' => 'El telefono debe ser una cadena de texto.',
            'telefono.unique' => 'Este telefono ya esta registrado.',

            'fecha_de_contratacion.required' => 'La fecha de contratacion es obligatoria.',
            'fecha_de_contratacion.date' => 'La fecha de contratacion debe ser una fecha valida.',

            'contacto_opcional.string' => 'El contacto opcional debe ser una cadena de texto.',

            'activo.required' => 'El estado activo es obligatorio.',
            'activo.integer' => 'El estado activo debe ser un numero entero.',
            'activo.in' => 'El estado activo debe ser 0 o 1.',
        ];
    }
}
