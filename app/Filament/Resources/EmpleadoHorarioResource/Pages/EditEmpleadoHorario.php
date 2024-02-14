<?php

namespace App\Filament\Resources\EmpleadoHorarioResource\Pages;

use App\Filament\Resources\EmpleadoHorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmpleadoHorario extends EditRecord
{
    protected static string $resource = EmpleadoHorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
