<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GrupoController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\PacienteController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// En caso de no importar, se debe usar Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers']
// apiResource devuelve las operaciones básicas incluyendo index, store, show, update y destroy
Route::group(['prefix' => 'v1'], function () {
    // apiResource dirige las peticiones a las funciones preconstruidas de laravel
    Route::apiResource('pacientes', PacienteController::class);
});

Route::group(['prefix' => 'v1'], function () {
    Route::get('horarios', [HorarioController::class, 'index']);
    Route::get('horarios/{dia}', [HorarioController::class, 'showByDay']);
});

Route::group(['prefix' => 'v1'], function () {
    Route::apiResource('empleados', EmpleadoController::class);
    Route::get('/empleados/{empleadoId}/horarios', [EmpleadoController::class, 'obtenerHorarios']);
    Route::post('empleados/{empleado}/asignar-horario', [EmpleadoController::class, 'asignarHorario']);
    Route::patch(
        'empleados/{empleado}/activar-desactivar-horario',
        [EmpleadoController::class, 'activar_desactivar_Horario']
    );
});

Route::group(['prefix' => 'v1'], function () {
    Route::apiResource('grupos', GrupoController::class);
    Route::get('grupos-con-empleados', [GrupoController::class, 'indexAll']);
});
