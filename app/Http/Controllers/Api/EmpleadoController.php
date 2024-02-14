<?php

namespace App\Http\Controllers\Api;

use App\Models\Horario;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Requests\UpdateEmpleadoRequest;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Recupera todos los registros del modelo Paciente
        $empleado = Empleado::all();

        // Retorna los registros como respuesta JSON
        return response()->json(['data' => $empleado], 200);
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
    public function store(StoreEmpleadoRequest $request)
    {
        // Método de la REST API para insertar un doctor
        // Almacenar el doctor en la base de datos
        Empleado::create($request->all());

        // Devolver una respuesta exitosa
        return response()->json(['message' => 'Empleado almacenado'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        // Verifica si el registro fue encontrado
        if ($empleado) {
            // Retorna el registro como respuesta JSON
            return response()->json(['data' => $empleado], 200);
        } else {
            // Retorna una respuesta indicando que el registro no fue encontrado
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empleado $empelado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmpleadoRequest $request, Empleado $empleado)
    {
        $empleado->update($request->all());
        return response()->json($empleado, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        // Puedes personalizar el mensaje de respuesta según tus necesidades
        $mensaje = 'Empleado eliminado correctamente';

        // Puedes incluir más información en el JSON de respuesta si es necesario
        $data = [
            'mensaje' => $mensaje,
        ];

        // Devuelve el JSON de respuesta con un código de estado 200 (éxito)
        return response()->json($data, 200);
    }

    public function obtenerHorarios($empleadoId)
    {
        // Obtener el empleado por ID con sus horarios
        $empleado = Empleado::with('horarios')->find($empleadoId);

        if (!$empleado) {
            return response()->json(['error' => 'Empleado no encontrado'], 404);
        }

        // Retornar los horarios del empleado en formato JSON
        return response()->json(['horarios' => $empleado->horarios]);
    }

    public function asignarHorario(Request $request, Empleado $empleado)
    {
        // Validar los datos recibidos en el cuerpo de la solicitud
        $validator = Validator::make($request->all(), [
            'dia_semana' => 'required|string',
            'hora_inicio' => 'required|date_format:H:i',
            // Agrega más reglas de validación según sea necesario
        ]);

        // Verificar si la validación falla y, en ese caso, devolver una respuesta de error
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Buscar el horario correspondiente a los datos proporcionados
        $horario = Horario::where('dia_semana', $request->input('dia_semana'))
            ->where('hora_inicio', $request->input('hora_inicio'))
            ->first();

        // Verificaciones
        if (!$horario) {
            // Si el horario no existe
            $response = ['message' => 'Horario inexistente'];
            $status = 404;
        } elseif ($empleado->horarios()->where('horario_id', $horario->id)->exists()) {
            // Si el empleado tiene asignado dicho horario
            $response = ['message' => 'Horario ya asignado al empleado'];
            $status = 400;
        } else {
            $empleado->horarios()->attach($horario->id, ['activo' => true]);
            $response = ['message' => 'Horario asignado correctamente', 'horario_asignado' => $horario];
            $status = 200;
        }

        return response()->json($response, $status);
    }

    public function activar_desactivar_Horario(Request $request, Empleado $empleado)
    {
        // Validar los datos recibidos en el cuerpo de la solicitud
        $validator = Validator::make($request->all(), [
            'dia_semana' => 'required|string',
            'hora_inicio' => 'required|date_format:H:i',
            // Agrega más reglas de validación según sea necesario
        ]);

        // Verificar si la validación falla y, en ese caso, devolver una respuesta de error
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Buscar el horario correspondiente a los datos proporcionados
        $horario = Horario::where('dia_semana', $request->input('dia_semana'))
            ->where('hora_inicio', $request->input('hora_inicio'))
            ->first();

        $accion = $request->input('accion', 'desactivar');

        if (!$horario) {
            // Verificar si se encontró el horario
            $response = ['message' => 'Horario inexistente'];
            $status = 404;
        } elseif (!$empleado->horarios()->where('horario_id', $horario->id)->exists()) {
            // Verifica si el horario está asignado al empleado
            $response = ['message' => 'El horario no está asignado al empleado'];
            $status = 404;
        } elseif ($accion === 'activar') {
            // Activa el horario
            $empleado->horarios()->updateExistingPivot($horario->id, ['activo' => 1]);
            $response = ['message' => 'Horario activado correctamente'];
            $status = 200;
        } elseif ($accion === 'desactivar') {
            // Desactiva el horario
            $empleado->horarios()->updateExistingPivot($horario->id, ['activo' => 0]);
            $response = ['message' => 'Horario desactivado correctamente'];
            $status = 200;
        } else {
            // Ingreso una opción nó válida
            $response = ['message' => 'Opción no válida'];
            $status = 400;
        }
        return response()->json($response, $status);
    }
}
