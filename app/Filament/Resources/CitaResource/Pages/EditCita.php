<?php

namespace App\Filament\Resources\CitaResource\Pages;

use App\Filament\Resources\CitaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCita extends EditRecord
{
    protected static string $resource = CitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['estado'] == 'Cancelado') {
            $data['fecha_fin_cita'] = $data['fecha_inicio_cita'];
            $data['fecha_inicio_cita'] = null;
        }
        return $data;
    }
}
