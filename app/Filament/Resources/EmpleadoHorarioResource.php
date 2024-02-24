<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\EmpleadoHorario;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmpleadoHorarioResource\Pages;
use App\Filament\Resources\EmpleadoHorarioResource\RelationManagers;

class EmpleadoHorarioResource extends Resource
{
    protected static ?string $model = EmpleadoHorario::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Organización';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('empleado_id')
                    ->relationship('empleado', 'nombre_completo')
                    ->searchable()
                    ->preload(20)
                    ->required()
                    ->native(false),
                Select::make('horario_id')
                    ->relationship('horario', 'descripcion_horario')
                    ->searchable()
                    ->preload(20)
                    ->native(false)
                    ->required(),
                Forms\Components\Toggle::make('activo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('empleado.nombre_completo')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('horario.descripcion_horario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->alignment(Alignment::Center)
                    ->boolean(),
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
                Tables\Actions\Action::make('Activar')
                    ->requiresConfirmation()
                    ->action(fn (EmpleadoHorario $record) => $record->update(['activo' => true])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('activar horarios')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-arrow-up')
                        ->color('info')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            // Aquí desactiva el campo "Activo" para cada registro
                            $record->update(['activo' => true]);
                        })),
                    BulkAction::make('desactivar horarios')
                        ->icon('heroicon-o-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            // Aquí desactiva el campo "Activo" para cada registro
                            $record->update(['activo' => false]);
                        })),
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
            'index' => Pages\ListEmpleadoHorarios::route('/'),
            'create' => Pages\CreateEmpleadoHorario::route('/create'),
            'edit' => Pages\EditEmpleadoHorario::route('/{record}/edit'),
        ];
    }
}
