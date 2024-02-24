<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use App\Models\Empleado;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmpleadoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmpleadoResource\RelationManagers;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    // Modifica el nombre del label tanto en el panel como en el formulario
    protected static ?string $modelLabel = 'Doctor';

    // Modifica el plural
    protected static ?string $pluralModelLabel = 'Doctores';

    // Modifica solamente el label del panel de navegación.
    protected static ?string $navigationLabel = 'Doctores';

    // Cambia tanto el nombre del label en el panel como en la información
    //protected static ?string $modelLabel = 'Mis citas';

    protected static ?string $navigationGroup = 'Control Médico';

    // Función que despliega la cantidad de usuarios generados como un badge
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Grupo e Información')
                    ->description('Información principal del empleado')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Select::make('grupo_id')
                            ->relationship('grupo', 'nombre')
                            ->suffixIcon('heroicon-o-user-group')
                            ->suffixIconColor('primary')
                            ->required()
                            ->native(false)
                            ->native(false)
                            ->createOptionForm([
                                Section::make('Grupo')
                                    ->description('Ingrese información del grupo')
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        TextInput::make('nombre')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(
                                                fn (Set $set, ?String $state)
                                                => $set('slug', Str::slug($state))
                                            )
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('slug')
                                            ->suffixIcon('heroicon-o-globe-alt')
                                            ->suffixIconColor('primary')
                                            ->required()
                                            ->maxLength(255),
                                    ])->columns(2), // La propiedad collapsible evita que se agrege required al input
                            ]),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(64),
                        TextInput::make('apellido')
                            ->required()
                            ->maxLength(64),
                        TextInput::make('edad')
                            ->suffixIcon('heroicon-o-cake')
                            ->suffixIconColor('primary')
                            ->required()
                            ->numeric(),
                    ])->columns(2),
                Section::make('Información de contacto')
                    ->description('Información relevante para el contacto')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextInput::make('correo')
                            ->required()
                            ->suffixIcon('heroicon-o-at-symbol')
                            ->suffixIconColor('primary')
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'El correo ya existe en nuestros registros.'
                            ])
                            ->maxLength(255),
                        TextInput::make('direccion')
                            ->suffixIcon('heroicon-o-home-modern')
                            ->suffixIconColor('primary')
                            ->maxLength(255),
                        TextInput::make('telefono')
                            ->tel()
                            ->suffixIcon('heroicon-o-device-phone-mobile')
                            ->suffixIconColor('primary')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Dicho número ya existe en nuestros registros.',
                            ])
                            ->maxLength(255),
                        TextInput::make('contacto_opcional')
                            ->suffixIcon('heroicon-o-phone')
                            ->suffixIconColor('primary')
                            ->maxLength(255),
                    ])->columns(2),
                Section::make('Asignación de horarios')
                    ->description('Escoga los turnos de trabajo')
                    ->schema([
                        Select::make('horarios_id')
                            ->multiple()
                            ->relationship(
                                'horarios',
                                'descripcion_horario',
                                modifyQueryUsing: function (Builder $query) {
                                    return $query->orderByRaw("FIELD(dia_semana, 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo')");
                                }
                            ) // Instrucción para ordenar el campo de selección basado en los días de la semana
                            ->preload(20)
                            ->searchable()
                            ->native(false),
                        // Repeater::make('empleadoHorario')
                        //     ->relationship()
                        //     ->label('Horarios')
                        //     ->reorderable()
                        //     ->collapsible()
                        //     ->collapsed()
                        //     ->simple(
                        //         Select::make('horario_id')
                        //             ->native(false)
                        //             ->relationship('horario', 'descripcion_horario')
                        //             ->required()
                        //             ->unique(ignoreRecord: true)
                        //             ->validationMessages([
                        //                 'unique' => 'Este horario ya está asignado'
                        //             ]),
                        //         // ...
                        //     )
                        //     ->hiddenOn('create')
                    ]),
                Section::make('Información extra')
                    ->description('Detalles relevantes del empleado')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->suffixIcon('heroicon-o-lock-closed')
                            ->suffixIconColor('primary')
                            ->required()
                            ->confirmed()
                            ->hiddenOn('edit')
                            ->validationMessages([
                                'confirmed' => 'Las contraseñas no coinciden.',
                            ])
                            ->maxLength(255),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->suffixIcon('heroicon-s-lock-closed')
                            ->suffixIconColor('primary')
                            ->required()
                            ->hiddenOn('edit')
                            ->maxLength(255),
                        DatePicker::make('fecha_de_contratacion')
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->suffixIconColor('primary')
                            ->maxDate(now())
                            ->minDate(now()->subYears(120))
                            ->default(now())
                            ->required(),
                        TextInput::make('activo')
                            ->hidden()
                            ->default(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grupo.nombre')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-s-user-group')
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('edad')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('correo')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Correo copiado')
                    ->icon('heroicon-o-envelope')
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('direccion')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('telefono')
                    ->icon('heroicon-o-phone')
                    ->iconColor('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_de_contratacion')
                    ->date()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('primary')
                    ->sortable()
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('contacto_opcional')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('activo')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('grupo.nombre')
            ->filters([
                SelectFilter::make('Grupo')
                    ->relationship('grupo', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\CitasRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
            'edit' => Pages\EditEmpleado::route('/{record}/edit'),
        ];
    }
}
