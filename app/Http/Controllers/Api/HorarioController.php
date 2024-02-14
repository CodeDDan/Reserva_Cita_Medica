<?php

namespace App\Http\Controllers\Api;

use App\Models\Horario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HorarioController extends Controller
{
    public function index()
    {
        // Recupera todos los registros del modelo Paciente
        $horarios = Horario::all();

        // Retorna los registros como respuesta JSON
        return response()->json(['data' => $horarios], 200);
    }

    public function showByDay($dia)
    {
        $horarios = Horario::where('dia_semana', $dia)->get();
        return response()->json($horarios, 200);
    }
}
