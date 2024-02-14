<?php

namespace App\Filament\Paciente\Widgets;

use App\Models\Cita;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Empleado;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Saade\FilamentFullCalendar\Actions\CreateAction;
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
                        ->afterStateUpdated(fn (Set $set) => $set('empleado_id', null))
                        ->native(false)
                        ->hiddenOn('view'),
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
                        ->required(),
                ])->columns(2),
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
                            'Agendado' => 'Agendado',
                            'Reservado' => 'Reservado',
                            'Cancelado' => 'Cancelado',
                            'Abandonado' => 'Abandonado'
                        ])
                        ->default('Agendado')
                        ->disabledOn('create'),
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
            return [
                'id' => $cita->id,
                'title' => $titulo,
                'start' => $cita->fecha_inicio_cita,
                'end' => $cita->fecha_fin_cita,
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

                    return $data;
                }),
            CreateAction::make()
                ->mountUsing(
                    function (Form $form, array $arguments) {
                        $form->fill([
                            'fecha_inicio_cita' => $arguments['start'] ?? null,
                            'fecha_fin_cita' => $arguments['end'] ?? null
                        ]);
                    }
                )
        ];
    }
}
