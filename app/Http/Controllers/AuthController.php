<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar al paciente por correo electrónico
        $paciente = Paciente::where('email', $request->email)->first();

        // Verificar si el paciente existe y las credenciales son válidas
        if ($paciente && Hash::check($request->password, $paciente->password)) {
            // Credenciales válidas, devolver el paciente como JSON
            return response()->json([
                'id' => $paciente->id,
                'nombre' => $paciente->nombre,
                'email' => $paciente->email,
            ]);
        }

        // Credenciales inválidas, devolver un mensaje de error
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }
}
