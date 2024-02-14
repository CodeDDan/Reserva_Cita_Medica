<?php

namespace App\Filament\Resources\EmpleadoHorarioResource\Pages;

use App\Filament\Resources\EmpleadoHorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmpleadoHorarios extends ListRecords
{
    protected static string $resource = EmpleadoHorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
