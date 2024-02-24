<?php

namespace App\Filament\Resources\CitaResource\Pages;

use Carbon\Carbon;
use App\Models\Cita;
use App\Models\Empleado;
use App\Filament\Resources\CitaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCita extends CreateRecord
{
    protected static string $resource = CitaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['empleado_id'] == null) {
            $data['empleado_id'] = $this->asignarDoctorEquitativo($data['fecha_inicio_cita'], $data['grupo_id']);
        }
        // Comprobamos si se asigno un empleado o no
        if ($data['empleado_id'] == null) {
            Notification::make()
                ->danger()
                ->color('danger')
                ->title('No se creó la cita')
                ->body('No hay disponibilidad para la fecha y especialidad seleccionada')
                ->send();
            // Detiene el flujo de ejecución
            $this->halt();
        }
        return $data;
    }

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

    public function asignarDoctorEquitativo($fecha, $grupo)
    {
        $fechaHora = $fecha;
        $idGrupo = $grupo;
        $idDoctoresGrupo = Empleado::obtenerIdPorGrupo($idGrupo);

        // Calcular cuántas citas tiene cada doctor en esa fecha y hora
        $idDoctoresDisponibles = $this->obtenerIdsDisponibles($fechaHora, $idDoctoresGrupo);

        // Si no encuentra doctores disponibles sale de la función y debe retornar un json sin doctores, además de un mensaje
        if (empty($idDoctoresDisponibles)) {
            // No hay id's disponibles de doctores
            return null;
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
            return $idDoctorAsignado;
        } else {
            // Si todos los doctores tienen la misma cantidad de citas, asignar al primer doctor de la lista
            return $idDoctoresDisponibles[0];
        }
    }
}
