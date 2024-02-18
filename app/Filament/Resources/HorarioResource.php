<?php

namespace App\Filament\Resources;

use DateTime;
use Filament\Forms;
use Filament\Tables;
use App\Models\Horario;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use PhpParser\Node\Stmt\Label;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\HorarioResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\HorarioResource\RelationManagers;
use Filament\Forms\Components\Section;

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
            $dateTime->modify('+1 hour');

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
                            ->options([
                                'Lunes' => 'Lunes',
                                'Martes' => 'Martes',
                                'Miercoles' => 'Miercoes',
                                'Jueves' => 'Jueves',
                                'Viernes' => 'Viernes',
                            ])
                            ->native(false)
                            ->columnSpanFull(),
                        Select::make('hora_inicio')
                            ->options([
                                '08:00' => '08:00',
                                '09:00' => '09:00',
                                '10:00' => '10:00',
                                '11:00' => '11:00',
                                '13:00' => '13:00',
                                '14:00' => '14:00',
                                '15:00' => '15:00',
                                '16:00' => '16:00',
                                '17:00' => '17:00',
                            ])
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?String $state) => $set('hora_fin', aumentarHora($state)))
                            ->required()
                            ->unique(modifyRuleUsing: function (Unique $rule, callable $get) {
                                return $rule
                                    ->where('dia_semana', $get('dia_semana'))
                                    ->where('hora_inicio', $get('hora_inicio'))
                                    ->where('hora_fin', $get('hora_fin'));
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Ya existe dicho horario'
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('hora_fin')
                            ->readOnly()
                            ->required(),
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
                Tables\Columns\TextColumn::make('descripcion_horario')
                    ->label('Horarios de atención'),
                Tables\Columns\IconColumn::make('estado')
                    ->alignment(Alignment::Center)
                    ->boolean(),
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
            'index' => Pages\ListHorarios::route('/'),
            'create' => Pages\CreateHorario::route('/create'),
            'edit' => Pages\EditHorario::route('/{record}/edit'),
        ];
    }
}
