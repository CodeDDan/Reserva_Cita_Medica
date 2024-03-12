<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Filament\Resources\PacienteResource\RelationManagers;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    // Modifica el nombre del label del panel
    //protected static ?string $navigationLabel = 'Ver citas';

    // Cambia tanto el nombre del label en el panel como en la información
    //protected static ?string $modelLabel = 'Mis citas';
    protected static ?string $navigationGroup = 'Control Médico';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información personal')
                    ->description('Por favor, proporcione la siguiente información
                        personal para facilitar la identificación y comunicación
                        efectiva. Todos los campos son obligatorios.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextInput::make('nombre')
                            ->regex('/^[A-Za-z\s]+$/')
                            ->required() // Propiedad requerida para el envío del formulario
                            ->maxLength(64) // Longitud máxima
                            ->validationMessages([
                                'regex' => 'Solo se admiten letras y espacios en el nombre'
                            ]),
                        TextInput::make('apellido')
                            ->regex('/^[A-Za-z\s]+$/')
                            ->required()
                            ->maxLength(64)
                            ->validationMessages([
                                'regex' => 'Solo se admiten letras y espacios en el apellido'
                            ]),
                        DatePicker::make('fecha_de_nacimiento')
                            ->native(false) // Le indicamos no usar el componente html por defecto
                            ->suffixIcon('heroicon-o-cake')
                            ->suffixIconColor('primary')
                            //->minDate(now()->subYears(150))
                            ->required()
                            ->maxDate(now()), // La fecha máxima de selección
                        TextInput::make('email')
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
                    ->description('Por favor, proporcione información necesaria para contactarnos con el paciente.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextInput::make('direccion')
                            ->regex('/^(?=.*[a-zA-Z]\s[a-zA-Z]).*$/')
                            ->validationMessages([
                                'regex' => 'Ingrese una dirección válida. Ejemplo (Monteolivo s2041)'
                            ])
                            ->suffixIcon('heroicon-o-home-modern')
                            ->suffixIconColor('primary')
                            ->maxLength(255),
                        TextInput::make('telefono')
                            ->tel()
                            ->telRegex('/^(\+593|593|09)(\d{8})$/')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->suffixIcon('heroicon-o-device-phone-mobile')
                            ->suffixIconColor('primary')
                            ->maxLength(255)
                            ->validationMessages([
                                'regex' => 'Ingrese un número ecuatoriano válido',
                                'unique' => 'El número telefónico ya existe en nuestros registros',
                            ]),
                        TextInput::make('contacto_opcional')
                            ->regex('/^(?=.*[a-zA-Z])(?=.*\d).*\s.*[a-zA-Z0-9].*$/')
                            ->validationMessages([
                                'regex' => 'Ingrese un contacto válido. Ejemplo: (Papá: 09xxxxx..)'
                            ])
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_de_nacimiento')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('direccion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contacto_opcional')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePaciente::route('/create'),
            'edit' => Pages\EditPaciente::route('/{record}/edit'),
        ];
    }
}
