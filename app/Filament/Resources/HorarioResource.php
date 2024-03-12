<?php

namespace App\Filament\Resources;

use DateTime;
use Filament\Forms;
use Filament\Tables;
use App\Models\Horario;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use PhpParser\Node\Stmt\Label;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Validation\Rules\Unique;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\HorarioResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\HorarioResource\RelationManagers;

class HorarioResource extends Resource
{
    protected static ?string $model = Horario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Organización';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        // Función para la transformación de la hora
        function aumentarHora($state)
        {
            // Crear un objeto DateTime con la hora proporcionada
            $dateTime = DateTime::createFromFormat('H:i', $state);

            // Aumentar la hora en 1
            $dateTime->modify('+30 minutes');

            // Formatear y devolver la hora aumentada
            return $dateTime->format('H:i');
        }

        return $form
            ->schema([
                Section::make('Personalización')
                    ->description('Seleccione el día y la hora de inicio')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Select::make('dia_semana')
                            ->required()
                            ->validationMessages([
                                'required' => 'Seleccione un día de la semana'
                            ])
                            ->options([
                                'Lunes' => 'Lunes',
                                'Martes' => 'Martes',
                                'Miercoles' => 'Miercoles',
                                'Jueves' => 'Jueves',
                                'Viernes' => 'Viernes',
                            ])
                            ->native(false)
                            ->columnSpanFull(),
                        Select::make('hora_inicio')
                            ->options([
                                '08:00' => '08:00',
                                '08:30' => '08:30',
                                '09:00' => '09:00',
                                '09:30' => '09:30',
                                '10:00' => '10:00',
                                '10:30' => '10:30',
                                '11:00' => '11:00',
                                '11:30' => '11:30',
                                '13:00' => '13:00',
                                '13:30' => '13:30',
                                '14:00' => '14:00',
                                '14:30' => '14:30',
                                '15:00' => '15:00',
                                '15:30' => '15:30',
                                '16:00' => '16:00',
                                '16:30' => '16:30',
                                '17:00' => '17:00',
                                '17:30' => '17:30',
                                '18:00' => '18:00',
                                '18:30' => '18:30',
                            ])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?String $state) {
                                if ($get('hora_inicio') !== null) {
                                    $set('hora_fin', aumentarHora($state));
                                }
                            })
                            ->required()
                            ->unique(modifyRuleUsing: function (Unique $rule, callable $get) {
                                return $rule
                                    ->where('dia_semana', $get('dia_semana'))
                                    ->where('hora_inicio', $get('hora_inicio'))
                                    ->where('hora_fin', $get('hora_fin'));
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Ya existe dicho horario.',
                                'required' => 'Seleccione una hora.'
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('hora_fin')
                            ->readOnly()
                            ->required()
                            ->validationMessages([
                                'unique' => 'Ya existe dicho horario.',
                                'required' => 'Se necesita una hora final'
                            ]),
                        Forms\Components\Toggle::make('estado')
                            ->onColor('success')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->inline(false)
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dia_semana')
                    ->label('Dia de atención')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->alignment(Alignment::Center)
                    ->label('Inicio del turno')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hora_fin')
                    ->alignment(Alignment::Center)
                    ->label('Fin del turno'),
                Tables\Columns\ToggleColumn::make('estado')
                    ->alignment(Alignment::Center)
                    ->onColor('success')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última edición')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('dia_semana')
                    ->multiple()
                    ->options([
                        'lunes' => 'lunes',
                        'martes' => 'martes',
                        'miercoles' => 'miercoles',
                        'jueves' => 'jueves',
                        'viernes' => 'viernes',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('activar horarios')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-arrow-up')
                        ->color('info')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            // Aquí desactiva el campo "Activo" para cada registro
                            $record->update(['estado' => true]);
                        })),
                    BulkAction::make('desactivar horarios')
                        ->icon('heroicon-o-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            // Aquí desactiva el campo "Activo" para cada registro
                            $record->update(['estado' => false]);
                        })),
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
            'index' => Pages\ListHorarios::route('/'),
            'create' => Pages\CreateHorario::route('/create'),
            'edit' => Pages\EditHorario::route('/{record}/edit'),
        ];
    }
}
