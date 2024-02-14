<?php

namespace App\Http\Controllers\Api;

use App\Models\Paciente;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Recupera todos los registros del modelo Paciente
        $pacientes = Paciente::all();

        // Retorna los registros como respuesta JSON
        return response()->json(['data' => $pacientes], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePacienteRequest $request)
    {
        // Método de la REST API para insertar un doctor
        // Almacenar el doctor en la base de datos
        Paciente::create($request->all());

        // Devolver una respuesta exitosa
        return response()->json(['message' => 'Paciente almacenado'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Paciente $paciente)
    {
        // El paciente ya está inyectado en el parámetro $paciente, no es necesario buscarlo de nuevo.
        // $paciente = Paciente::find($paciente);
        // Verifica si el registro fue encontrado
        if ($paciente) {
            // Retorna el registro como respuesta JSON
            return response()->json(['data' => $paciente], 200);
        } else {
            // Retorna una respuesta indicando que el registro no fue encontrado
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paciente $paciente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePacienteRequest $request, Paciente $paciente)
    {
        $paciente->update($request->all());
        return response()->json($paciente, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paciente $paciente)
    {
        // Elimina el paciente
        $paciente->delete();

        // Puedes personalizar el mensaje de respuesta según tus necesidades
        $mensaje = 'Paciente eliminado correctamente';

        // Puedes incluir más información en el JSON de respuesta si es necesario
        $data = [
            'mensaje' => $mensaje,
        ];

        // Devuelve el JSON de respuesta con un código de estado 200 (éxito)
        return response()->json($data, 200);
    }
}
