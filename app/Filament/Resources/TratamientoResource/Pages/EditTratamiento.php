<?php

namespace App\Filament\Resources\TratamientoResource\Pages;

use App\Filament\Resources\TratamientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTratamiento extends EditRecord
{
    protected static string $resource = TratamientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
