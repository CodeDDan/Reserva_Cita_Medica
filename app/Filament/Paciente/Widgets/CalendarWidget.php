<?php

namespace App\Filament\Paciente\Widgets;

use App\Models\Cita;
use App\Models\Horario;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Empleado;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use App\Filament\Resources\CitaResource\Pages\CreateCita;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Cita::class;

    public function getFormSchema(): array
    {
        $formSchema = [
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
                        ->requiredUnless('empleado_id', !null)
                        ->afterStateUpdated(fn (Set $set) => $set('empleado_id', null))
                        ->validationMessages([
                            'required_unless' => 'Especialidad no seleccionada',
                        ])
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
                        ->searchable()
                        ->preload()
                        ->live(onBlur: true)
                        ->native(false)
                        ->suffixIcon('heroicon-o-user-plus')
                        ->suffixIconColor('primary'),
                ])->columns(2),
            Section::make('Información')
                ->description('Agrege información sobre el motivo de la consulta.')
                ->icon('heroicon-o-document-minus')
                ->schema([
                    RichEditor::make('motivo') // Ocasiona un error de label
                    // ->fileAttachmentsDisk('local') // Configurar el disco como local
                    // ->fileAttachmentsDirectory('C:\Users\Daniel\Documents\filament_diagnostico_imagenes') // Especificar la carpeta de almacenamiento
                    // ->fileAttachmentsVisibility('public') // Establecer la visibilidad de los archivos adjuntos
                ]),
            Section::make('Estado y fechas')
                ->description('Agrege el estado de la cita y la fecha de la cita')
                ->icon('heroicon-o-calendar')
                ->schema([
                    DatePicker::make('dia_cita')
                        ->hiddenOn('edit')
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
                        ->hiddenOn('edit')
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
                                } else if ($get('dia_cita')) {
                                    $dia_semana = '';
                                    // Si hay una fecha seleccionada, obtener el día de la semana
                                    $fecha = Carbon::createFromFormat('Y-m-d H:i:s', $get('dia_cita'));
                                    $dia_semana = strtolower($fecha->translatedFormat('l'));

                                    return $query->where('dia_semana', $dia_semana);
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
                        ->disabled()
                        ->required()
                        ->dehydrated(true) // Para que se siga enviando el valor del campo a pesar de que esté deshabilitado
                        ->unique(modifyRuleUsing: function (Unique $rule, callable $get) {
                            return $rule
                                ->where(function ($query) use ($get) {
                                    $query->where('paciente_id', $get('paciente_id'))
                                        ->orWhere('empleado_id', $get('empleado_id'));
                                })
                                ->where('fecha_inicio_cita', $get('fecha_inicio_cita'));
                        }, ignoreRecord: true)
                        // Se cambia la validación de fecha mínima al formulario de creacion para no interferir con la edición
                        ->validationMessages([
                            'unique' => 'Fecha no disponible (Doctor o Paciente ya asignados a esta fecha y hora)',
                            'required' => 'Seleccione la fecha y hora de la reserva',
                        ])->native(false),
                    DateTimePicker::make('fecha_fin_cita')
                        ->readOnly()
                        ->suffixIcon('heroicon-s-calendar')
                        ->suffixIconColor('primary')
                        ->hiddenOn('create')
                        ->native(false),
                ])->columns(2),
        ];

        return $formSchema;
    }

    public function fetchEvents(array $fetchInfo): array
    {
        // Obtener el ID del paciente logeado
        $pacienteId = Auth::id();

        // Filtrar las citas del paciente logeado dentro del rango de fechas proporcionado
        $citas = Cita::where('paciente_id', $pacienteId)
            ->where('fecha_inicio_cita', '>=', $fetchInfo['start'])
            ->get();

        // Mapear las citas a un formato adecuado para FullCalendar
        $eventos = $citas->map(function ($cita) {

            if (!$cita->fecha_fin_cita) {
                $cita->fecha_fin_cita = $cita->fecha_inicio_cita;
            }

            // crearemos nuestro título personalizado
            $titulo = $cita->empleado->grupo->nombre . ' - Dr. ' . $cita->empleado->nombre_completo;

            // Definir el color del evento basado en el estado de la cita
            $color = '#4287f5'; // Azul por defecto

            // Ejemplo: cambiar el color a verde si el estado de la cita es "confirmada"
            if ($cita->estado == 'Reservado') {
                $color = '#18b00b'; // Verde
            } elseif ($cita->estado == 'Abandonado') {
                $color = '#b8260d'; // Rojo
            }

            $fecha_actual = Carbon::now();
            $fecha_comparacion = Carbon::parse($cita->fecha_inicio_cita);

            if ($fecha_comparacion->lessThan($fecha_actual)) {
                $color = $this->aplicarTonoOpaco($color);
            }

            return [
                'id' => $cita->id,
                'title' => $titulo,
                'start' => $cita->fecha_inicio_cita,
                'end' => $cita->fecha_fin_cita,
                'color' => $color, // Asignar el color al evento
            ];
        });

        return $eventos->toArray();
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['paciente_id'] = auth()->id();
                    $data['estado'] = 'Agendado';
                    if ($data['empleado_id'] == null) {
                        $citaResourceCreacion = new CreateCita();
                        $data['empleado_id'] = $citaResourceCreacion->asignarDoctorEquitativo($data['fecha_inicio_cita'], $data['grupo_id']);
                    }
                    return $data;
                })
                ->mountUsing(
                    function (Form $form, array $arguments) {
                        $form->fill([
                            'dia_cita' => $arguments['start'] ?? null,
                            'fecha_fin_cita' => $arguments['end'] ?? null
                        ]);
                    }
                )
                ->before(function (CreateAction $action, array $data) {
                    $currentDateTime = Carbon::now();
                    $fecha_cita = Carbon::parse($data['fecha_inicio_cita']);

                    if ($fecha_cita->lessThan($currentDateTime)) {
                        Notification::make()
                            ->danger()
                            ->color('danger')
                            ->title('Fecha incorrecta')
                            ->body('La fecha de la cita debe ser mayor a la fecha y hora actual')
                            ->send();

                        // Detiene la ejecución del Action
                        $action->halt();
                    }
                    if ($data['empleado_id'] == null) {
                        Notification::make()
                            ->danger()
                            ->color('danger')
                            ->title('Sin disponibilidad')
                            ->body('No hay doctores disponibles para la fecha y hora seleccionada')
                            ->send();
                        // Detiene la ejecución del Action
                        $action->halt();
                    }
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            // Desactivamos las acciones de edición y borrado para el paciente
            // Los imports de las acciones deben ser de sadee
            // Actions\EditAction::make(),
            DeleteAction::make(),
        ];
    }

    private function aplicarTonoOpaco($color)
    {
        // Convertir el color hexadecimal a RGB
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));

        // Aplicar un tono más opaco (reducir la intensidad de cada componente RGB)
        $r = round($r * 0.6);
        $g = round($g * 0.6);
        $b = round($b * 0.6);

        // Convertir el nuevo color RGB de nuevo a formato hexadecimal
        $color = sprintf("#%02x%02x%02x", $r, $g, $b);

        return $color;
    }
}
