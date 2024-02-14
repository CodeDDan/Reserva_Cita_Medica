<?php

namespace App\Filament\Resources\TratamientoResource\Pages;

use App\Filament\Resources\TratamientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTratamientos extends ListRecords
{
    protected static string $resource = TratamientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
