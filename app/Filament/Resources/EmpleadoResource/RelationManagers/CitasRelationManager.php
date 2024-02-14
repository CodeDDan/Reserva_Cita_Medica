<?php

namespace App\Filament\Resources\EmpleadoResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CitasRelationManager extends RelationManager
{
    protected static string $relationship = 'citas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('paciente_id')
                    ->label('Paciente')
                    ->relationship('paciente', 'nombre_completo')
                    ->required()
                    ->disabledOn('edit')
                    ->validationMessages([
                        'required' => 'Escoga un paciente.',
                    ])
                    ->native(false)
                    ->suffixIcon('heroicon-o-user')
                    ->suffixIconColor('primary')
                    ->createOptionForm([
                        Section::make('Información personal')
                            ->description('Por favor, proporcione la siguiente información
                                personal para facilitar la identificación y comunicación efectiva.
                                Todos los campos son obligatorios.')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                TextInput::make('nombre')
                                    ->required() // Propiedad requerida para el envío del formulario
                                    ->maxLength(64), // Longitud máxima
                                TextInput::make('apellido')
                                    ->required()
                                    ->maxLength(64),
                                DatePicker::make('fecha_de_nacimiento')
                                    ->native(false) // Le indicamos no usar el componente html por defecto
                                    ->suffixIcon('heroicon-o-cake')
                                    ->suffixIconColor('primary')
                                    //->minDate(now()->subYears(150))
                                    ->maxDate(now()), // La fecha máxima de selección
                                TextInput::make('correo')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->suffixIcon('heroicon-o-at-symbol')
                                    ->suffixIconColor('primary')
                                    ->validationMessages([
                                        'unique' => 'El correo ya existe en nuestros registros.',
                                    ])
                                    ->columnSpanFull()
                                    ->maxLength(255),
                            ])->columns(3),
                        Section::make('Información de contacto')
                            ->description('Por favor, proporcione información necesaria para contactarnos con el
                                paciente.')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                TextInput::make('direccion')
                                    ->suffixIcon('heroicon-o-home-modern')
                                    ->suffixIconColor('primary')
                                    ->maxLength(255),
                                TextInput::make('telefono')
                                    ->tel()
                                    ->required()
                                    ->suffixIcon('heroicon-o-device-phone-mobile')
                                    ->suffixIconColor('primary')
                                    ->maxLength(255),
                                TextInput::make('contacto_opcional')
                                    ->suffixIcon('heroicon-o-phone')
                                    ->suffixIconColor('primary')
                                    ->maxLength(255),
                                Select::make('activo')
                                    ->hiddenOn('create')
                                    ->label('Estado')
                                    ->suffixIcon('heroicon-o-ellipsis-horizontal-circle')
                                    ->suffixIconColor('primary')
                                    ->options([
                                        '1' => 'Activado',
                                        '0' => 'Desactivado'
                                    ])
                                    ->native(false)
                                    ->required()
                                    ->default(1),
                            ])->columns(2),
                        Section::make('Seguridad')
                            ->description('Añada las credenciales para el inicio de sesión')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                TextInput::make('password')
                                    ->label('Contraseña')
                                    ->suffixIcon('heroicon-o-lock-closed')
                                    ->suffixIconColor('primary')
                                    ->password()
                                    ->autocomplete(false)
                                    ->required()
                                    ->confirmed()
                                    ->validationMessages([
                                        'confirmed' => 'Las contraseñas no coinciden.',
                                    ])
                                    ->maxLength(255),
                                TextInput::make('password_confirmation')
                                    ->label('Confirmar contraseña')
                                    ->suffixIcon('heroicon-s-lock-closed')
                                    ->suffixIconColor('primary')
                                    ->password()
                                    ->autocomplete(false)
                                    ->required()
                                    ->maxLength(255),
                            ])->columns(2)
                            ->hiddenOn('edit'),
                    ]),
                Section::make('Información')
                    ->description('Agrege información sobre el motivo de la consulta.')
                    ->icon('heroicon-o-document-minus')
                    ->schema([
                        RichEditor::make('motivo')
                            ->required(),
                    ]),
                Section::make('Estado y fechas')
                    ->description('Agrege el estado de la cita y la fecha de la cita')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Select::make('estado')
                            ->required()
                            ->validationMessages([
                                'required' => 'Escoga el estado inicial de la cita.',
                            ])
                            ->suffixIcon('heroicon-o-clipboard-document-check')
                            ->suffixIconColor('primary')
                            ->native(false)
                            ->options([
                                'agendada',
                                'reservada',
                                'cancelada',
                                'abandonada'
                            ])
                            ->hiddenOn('create')
                            ->default(0),
                        DateTimePicker::make('fecha_inicio_cita')
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->suffixIconColor('primary')
                            ->minDate(now())
                            ->hoursStep(1)
                            ->minutesStep(15)
                            ->required()
                            ->validationMessages([
                                'required' => 'Escoga la fecha inicial de la cita.',
                            ]),
                        DateTimePicker::make('fecha_fin_cita')
                            ->native(false)
                            ->suffixIcon('heroicon-s-calendar')
                            ->suffixIconColor('primary')

                            ->hiddenOn('create'),
                    ])->columns(2),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('empleado_id')
            ->columns([
                Tables\Columns\TextColumn::make('paciente.nombre'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
