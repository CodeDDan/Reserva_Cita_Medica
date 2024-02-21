<?php

namespace App\Http\Controllers\Api;

use App\Models\Cita;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
            $numCitas = Cita::where('fecha_inicio_cita', $fechaHora)
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

    public function asignarDoctor(Request $request)
    {
        // Parsear el JSON recibido
        $data = $request->json()->all();
        $fechaHora = $data['fecha_inicio_cita'];
        $grupo = $data['grupo'];

        // Obtener la lista de doctores del grupo especificado (aquí deberías obtenerla de tu lógica)
        $idsDoctoresGrupo = [1, 2, 3, 4, 5]; // Suponiendo que tienes la lista de IDs de doctores por grupo

        // Devolver el ID del doctor encontrado
        return response()->json(['id_doctor_asignado' => $idsDoctoresGrupo]);
    }
}
