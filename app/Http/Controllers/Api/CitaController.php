<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Cita;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitaRequest;

class CitaController extends Controller
{
    public function obtenerIdsDisponibles($fechaHora, $idDoctores)
    {
        // Consultar la tabla de citas para verificar la disponibilidad de cada doctor en esa fecha y hora
        $doctoresConCita = Cita::where('fecha_inicio_cita', $fechaHora)
            ->whereIn('empleado_id', $idDoctores)
            ->pluck('empleado_id')
            ->toArray();

        // Filtrar los doctores disponibles para eliminar aquellos que ya tienen una cita programada en esa fecha y hora
        $doctoresDisponibles = array_diff($idDoctores, $doctoresConCita);

        // Asignar al primer doctor disponible que cumpla con los criterios de disponibilidad
        if (!empty($doctoresDisponibles)) {
            return $doctoresDisponibles;
        } else {
            // No hay doctores disponibles para esa fecha y hora
            return false;
        }
    }

    public function asignarDoctorEquitativo(Request $request)
    {
        // Parsear el JSON recibido
        $data = $request->json()->all();
        $fechaHora = $data['fecha_inicio_cita'];
        $idGrupo = $data['grupo'];
        $idDoctoresGrupo = Empleado::obtenerIdPorGrupo($idGrupo);

        // Calcular cuántas citas tiene cada doctor en esa fecha y hora
        $idDoctoresDisponibles = $this->obtenerIdsDisponibles($fechaHora, $idDoctoresGrupo);

        // Si no encuentra doctores disponibles sale de la función y debe retornar un json sin doctores, además de un mensaje
        if (empty($idDoctoresDisponibles)) {
            return response()->json([
                'encontrado' => false,
                'mensaje' => 'No hay doctores disponibles en esa fecha y hora'
            ]);
        }

        $citasPorDoctor = [];
        foreach ($idDoctoresDisponibles as $idDoctor) {
            // Calcular el inicio y fin de la semana para la fecha dada
            $fechaInicioSemana = Carbon::parse($fechaHora)->startOfWeek();
            $fechaFinSemana = Carbon::parse($fechaHora)->endOfWeek();

            // Contar las citas dentro de la semana para el doctor actual
            $numCitas = Cita::where('fecha_inicio_cita', '>=', $fechaInicioSemana)
                ->where('fecha_inicio_cita', '<=', $fechaFinSemana)
                ->where('empleado_id', $idDoctor)
                ->count();

            $citasPorDoctor[$idDoctor] = $numCitas;
        }

        // Encontrar al doctor con la menor cantidad de citas programadas
        $minCitas = min($citasPorDoctor);
        $doctoresMenosOcupados = array_keys($citasPorDoctor, $minCitas);

        // Asignar al primer doctor menos ocupado de la lista de doctores menos ocupados
        if (!empty($doctoresMenosOcupados)) {
            $idDoctorAsignado = reset($doctoresMenosOcupados);
            return response()->json([
                'encontrado' => true,
                'empleado_id' => $idDoctorAsignado
            ]);
        } else {
            // Si todos los doctores tienen la misma cantidad de citas, asignar al primer doctor de la lista
            return response()->json([
                'encontrado' => true,
                'empleado_id' => $idDoctoresDisponibles[0]
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCitaRequest $request)
    {
        // Obtener la fecha y hora actual del sistema
        $fecha_actual = Carbon::now();

        // Obtener los datos de la solicitud
        $data = $request->all();

        // Verificar si la fecha de inicio de la cita es menor que la fecha actual
        if (Carbon::parse($data['fecha_inicio_cita'])->lessThan($fecha_actual)) {
            // Si es menor, devolver un mensaje de error
            return response()->json(['error' => 'La fecha de inicio de la cita no puede ser menor que la fecha actual'], 400);
        }

        // Asegurarse de que el estado sea 'Agendado'
        $data['estado'] = 'Agendado';

        try {
            // Intentar crear la cita
            Cita::create($request->all());
            // Devolver una respuesta exitosa si la creación es exitosa
            return response()->json(['mensaje' => 'Cita almacenada'], 201);
        } catch (\Exception $exception) {
            // Devolver un mensaje de error genérico si la cita no se puede crear
            return response()->json(['error' => 'Cita no creada'], 500);
        }
    }

    public function getCitasUsuario($paciente_id)
    {
        // Recupera las citas del usuario dado el $paciente_id
        $citas = Cita::where('paciente_id', $paciente_id)->get();

        // Puedes agregar alguna lógica adicional aquí si es necesario

        // Devuelve las citas en formato JSON
        return response()->json($citas);
    }
}
