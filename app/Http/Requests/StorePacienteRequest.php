<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z]+\s?[A-Za-z]+$/'],
            'apellido' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z]+\s?[A-Za-z]+$/'],
            'fecha_de_nacimiento' => ['required', 'date'],
            'password' => ['required', 'string'], // Asegúrate de ajustar las reglas según tus necesidades
            'correo' => ['required', 'email', 'unique:pacientes'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['required', 'string', 'unique:pacientes'],
            'contacto_opcional' => ['nullable', 'string'],
            'activo' => ['required', 'integer', 'in:0,1'], // Se espera que 'activo' sea 0 o 1
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages()
    {
        // Pasar los mensajes sin acentos para que se despliguen mejor en la consola
        return [
            'nombre' => [
                'required' => 'El nombre es obligatorio.',
                'string' => 'El nombre debe ser una cadena de texto.',
                'max' => 'El nombre no puede tener mas de 64 caracteres.',
                'regex' => 'El nombre solo puede contener letras y un espacio entre ellas.',
            ],

            'apellido' => [
                'required' => 'El apellido es obligatorio.',
                'string' => 'El apellido debe ser una cadena de texto.',
                'max' => 'El apellido no puede tener mas de 64 caracteres.',
                'regex' => 'El apellido solo puede contener letras y un espacio entre ellas.',
            ],

            'fecha_de_nacimiento' => [
                'required' => 'La fecha de nacimiento es obligatoria.',
                'date' => 'La fecha de nacimiento debe ser una fecha valida.',
            ],

            'password' => [
                'required' => 'La password es obligatoria.',
                'string' => 'La password debe ser una cadena de texto.',
            ],

            'correo' => [
                'required' => 'El correo electronico es obligatorio.',
                'email' => 'El correo electronico debe ser una direccion de correo valida.',
                'unique' => 'Este correo electronico ya esta registrado.',
            ],

            'direccion' => [
                'string' => 'La direccion debe ser una cadena de texto.',
            ],

            'telefono' => [
                'required' => 'El telefono es obligatorio.',
                'string' => 'El telefono debe ser una cadena de texto.',
                'unique' => 'Este telefono ya esta registrado.',
            ],

            'contacto_opcional' => [
                'string' => 'El contacto opcional debe ser una cadena de texto.',
            ],

            'activo' => [
                'required' => 'El estado activo es obligatorio.',
                'integer' => 'El estado activo debe ser un numero entero.',
                'in' => 'El estado activo debe ser 0 o 1.',
            ],
        ];
    }
}
