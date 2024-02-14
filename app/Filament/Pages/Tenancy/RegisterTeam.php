<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Grupo;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Registrar Grupo';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre'), // Colocar bien el nombre de la tabla
                TextInput::make('slug'),
            ]);
    }

    protected function handleRegistration(array $data): Grupo
    {
        $team = Grupo::create($data);

        $team->miembros()->attach(auth()->user());

        return $team;
    }
}
