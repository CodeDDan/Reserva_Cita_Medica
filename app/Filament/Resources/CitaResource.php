<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Cita;
use Filament\Tables;
use App\Models\Grupo;
use App\Models\Horario;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Empleado;
use App\Models\Paciente;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\CitaResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CitaResource\RelationManagers;

class CitaResource extends Resource
{
    protected static ?string $model = Cita::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    // Modifica el nombre del label del panel
    //protected static ?string $navigationLabel = 'Ver citas';

    // Cambia tanto el nombre del label en el panel como en la información
    //protected static ?string $modelLabel = 'Mis citas';


    protected static ?string $navigationGroup = 'Control Médico';

    // Especifíca la ubicación en el panel
    //protected static ?string $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Área y doctor')
                    ->description('Por favor, agrege el área y personal de la cita')
                    ->icon('heroicon-o-heart')
                    ->schema([
                        Select::make('grupo_id')
                            ->relationship('empleado.grupo', 'nombre')
                            ->label('Especialidad')
                            ->suffixIcon('heroicon-o-identification')
                            ->suffixIconColor('primary')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set) => $set('empleado_id', null))
                            ->native(false),
                        Select::make('empleado_id')
                            ->label('Doctor')
                            ->relationship(
                                'empleado',
                                'nombre_completo',
                                modifyQueryUsing: function (Builder $query, Get $get) {
                                    return $query->where('grupo_id', $get('grupo_id'));
                                }
                            )
                            // Se debe colocar lo siguiente para que en la edición de la
                            // columna aparezca el nombre y no el id
                            ->getOptionLabelUsing(fn ($value): ?string => Empleado::find($value)?->nombre_completo)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(onBlur: true)
                            ->validationMessages([
                                'required' => 'Escoga un doctor.',
                            ])
                            ->native(false)
                            ->suffixIcon('heroicon-o-user-plus')
                            ->suffixIconColor('primary')
                            ->createOptionForm([
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
                                            ->native(false),
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
                                    ])->columns(2)
                            ])
                            ->required(),
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
                                    ->description('Por favor, proporcione la siguiente información personal
                                        para facilitar la identificación y comunicación efectiva.
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
                                    ->description('Por favor, proporcione información necesaria para
                                        contactarnos con el paciente.')
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
                            ])
                            ->unique(modifyRuleUsing: function (Unique $rule, callable $get) {
                                return $rule
                                    ->where('paciente_id', $get('paciente_id'))
                                    ->where('fecha_inicio_cita', $get('fecha_inicio_cita'));
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Ya tiene una cita asignada para dicha fecha y hora'
                            ]),
                    ])->columns(2),
                Section::make('Información')
                    ->description('Agrege información sobre el motivo de la consulta.')
                    ->icon('heroicon-o-document-minus')
                    ->schema([
                        RichEditor::make('motivo') // Ocasiona un error de label
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
                                'Agendado' => 'Agendado',
                                'Reservado' => 'Reservado',
                                'Cancelado' => 'Cancelado',
                                'Abandonado' => 'Abandonado'
                            ])
                            ->default('Agendado')
                            ->disabledOn('create'),
                        DatePicker::make('dia_cita')
                            ->format('m-d-Y')
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->suffixIconColor('primary')
                            ->validationMessages([
                                'required' => 'Escoga la fecha inicial de la cita.',
                            ])
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set) => $set('hora_cita', null)),
                        Select::make('hora_cita')
                            ->label('Hora de la cita')
                            ->relationship(
                                // Se debe ir relación por relación.
                                // Caso contrario se pierde el id para la selección
                                'empleado.empleadoHorario.horario',
                                'hora_inicio',
                                modifyQueryUsing: function (Builder $query, Get $get) {
                                    if ($get('empleado_id') && $get('dia_cita')) {
                                        $dia_semana = '';
                                        // Si hay una fecha seleccionada, obtener el día de la semana
                                        $fecha = Carbon::createFromFormat('Y-m-d H:i:s', $get('dia_cita'));
                                        $dia_semana = strtolower($fecha->translatedFormat('l'));

                                        return $query->whereHas('empleados', function ($query) use ($get, $dia_semana) {
                                            $query->where('empleado_id', $get('empleado_id'))->where('dia_semana', $dia_semana);
                                        });
                                    } else {
                                        // Esta condición se debe agregar para que no se cargue nada
                                        // Caso contrario se cargará la relación completa
                                        return $query->whereNull('id'); 
                                    }
                                }
                            )
                            ->native(false)
                            ->live(onBlur: true)
                            ->preload() // No deberían existir muchos horarios
                            ->searchable()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                if ($get('dia_cita') && $get('hora_cita')) {
                                    // Obtener el ID del horario desde la cita
                                    $horario_id = $get('hora_cita');

                                    // Recuperar el horario real de la base de datos utilizando el ID
                                    $horario = Horario::find($horario_id);

                                    // Obtener la hora de inicio del horario
                                    $hora_cita = $horario->hora_inicio;

                                    // Obtener la fecha de la cita y quitar la parte de la hora
                                    $dia_cita = date('Y-m-d', strtotime($get('dia_cita')));

                                    // Combinar los valores en fecha_inicio_cita
                                    $fecha_inicio_cita = $dia_cita . ' ' . $hora_cita;

                                    // Establecer el valor de fecha_inicio_cita
                                    $set('fecha_inicio_cita', $fecha_inicio_cita);
                                } else {
                                    $set('fecha_inicio_cita', null);
                                }
                            }),
                        DateTimePicker::make('fecha_inicio_cita')
                            ->unique(modifyRuleUsing: function (Unique $rule, callable $get) {
                                return $rule
                                    ->where('empleado_id', $get('empleado_id'))
                                    ->where('fecha_inicio_cita', $get('fecha_inicio_cita'));
                            }, ignoreRecord: true)
                            ->minDate(now())
                            ->validationMessages([
                                'unique' => 'Fecha para la cita no disponible',
                            ])
                            ->native(false)
                            ->readOnly(),
                        DateTimePicker::make('fecha_fin_cita')
                            ->readOnly()
                            ->suffixIcon('heroicon-s-calendar')
                            ->suffixIconColor('primary')
                            ->hiddenOn('create')
                            ->native(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('empleado.grupo.nombre')
                    ->icon('heroicon-o-user-group')
                    ->iconColor('primary')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('paciente.nombre_completo')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('empleado.nombre_completo')
                    ->label('Doctor')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Agendado' => 'info',
                        'Reservado' => 'success',
                        'Cancelado' => 'warning',
                        'Abandonado' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_inicio_cita')
                    ->icon('heroicon-o-calendar-days')
                    ->iconColor('primary')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('motivo')
                    ->html()
                    ->wrap(5)
                    ->words(8)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fecha_fin_cita')
                    ->dateTime()
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
            ->filters([
                SelectFilter::make('Especialidad')
                    ->relationship('empleado.grupo', 'nombre')
                    ->native(false),
                SelectFilter::make('Estado')
                    ->native(false)
                    ->options([
                        'Agendado' => 'Agendado',
                        'Reservado' => 'Reservado',
                        'Cancelado' => 'Cancelado',
                        'Abandonado' => 'Abandonado'
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Creado desde')
                            ->placeholder('Fecha inicial')
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->suffixIconColor('primary'),
                        DatePicker::make('created_until')
                            ->label('Creado hasta')
                            ->after('created_from')
                            ->placeholder('Fecha final')
                            ->native(false)
                            ->suffixIcon('heroicon-s-calendar')
                            ->suffixIconColor('primary'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Creado desde ' . Carbon::parse($data['created_from'])
                                ->locale('es-ES')
                                ->isoFormat('ll');
                            // al colocar 'llll' se incluye la hora y am o pm
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Creado hasta ' . Carbon::parse($data['created_until'])
                                ->locale('es-ES')
                                ->isoFormat('ll');
                        }

                        return $indicators;
                    })
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
            'index' => Pages\ListCitas::route('/'),
            'create' => Pages\CreateCita::route('/create'),
            'edit' => Pages\EditCita::route('/{record}/edit'),
        ];
    }
}
