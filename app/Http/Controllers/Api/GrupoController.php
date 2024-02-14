<?php

namespace App\Http\Controllers\Api;

use App\Models\Grupo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrupoRequest;
use App\Http\Requests\UpdateGrupoRequest;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Recupera todos los registros del modelo Paciente
        $grupo = Grupo::all();

        // Retorna los registros como respuesta JSON
        return response()->json(['data' => $grupo], 200);
    }

    public function indexAll()
    {
        $grupo = Grupo::with('empleados')->get();
        
        return response()->json(['data' => $grupo], 200);
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
    public function store(StoreGrupoRequest $request)
    {
        // Obtener el nombre del grupo desde la solicitud
        $nombre = $request->input('nombre');

        // Generar el slug a partir del nombre
        $slug = Str::slug($nombre);

        // Crear un nuevo grupo con el nombre y el slug
        $grupo = Grupo::create([
            'nombre' => $nombre,
            'slug' => $slug,
        ]);

        // Devolver una respuesta exitosa con el grupo creado
        return response()->json(['message' => 'Grupo almacenado', 'grupo' => $grupo], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Grupo $grupo)
    {
        // Verifica si el registro fue encontrado
        if ($grupo) {
            // Retorna el registro como respuesta JSON
            return response()->json(['data' => $grupo], 200);
        } else {
            // Retorna una respuesta indicando que el registro no fue encontrado
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grupo $grupo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGrupoRequest $request, Grupo $grupo)
    {
        $grupo->update($request->all());
        return response()->json($grupo, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();

        // Puedes personalizar el mensaje de respuesta según tus necesidades
        $mensaje = 'Grupo eliminado correctamente';

        // Puedes incluir más información en el JSON de respuesta si es necesario
        $data = [
            'mensaje' => $mensaje,
        ];

        // Devuelve el JSON de respuesta con un código de estado 200 (éxito)
        return response()->json($data, 200);
    }
}
