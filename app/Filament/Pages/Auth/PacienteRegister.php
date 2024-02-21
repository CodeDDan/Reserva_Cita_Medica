<?php

namespace App\Filament\Pages\Auth;

use Faker\Provider\ar_EG\Text;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;

class PacienteRegister extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNombreFormComponent(),
                        $this->getApellidoFormComponent(),
                        // $this->getNameFormComponent(),
                        $this->getEmailFormComponent()
                            ->autocomplete()
                            ->columnSpanFull(),
                        $this->getPasswordFormComponent()->autocomplete(),
                        $this->getPasswordConfirmationFormComponent()->autocomplete(),
                        $this->getFechaNacimientoComponent(),
                        $this->getTelefonoComponent(),
                    ])
                    ->columns(2)
                    ->statePath('data'),
            ),
        ];
    }

    protected function getNombreFormComponent(): Component
    {
        return TextInput::make('nombre')
            ->required();
    }

    protected function getApellidoFormComponent(): Component
    {
        return TextInput::make('apellido')
            ->required();
    }

    protected function getFechaNacimientoComponent(): Component
    {
        return DatePicker::make('fecha_de_nacimiento')
            ->native(false)
            ->maxDate(now()) // La fecha máxima de selección
            ->minDate(now()->subYears(150))
            ->default(now())
            ->required();
    }

    protected function getTelefonoComponent(): Component
    {
        return TextInput::make('telefono');
    }
}
