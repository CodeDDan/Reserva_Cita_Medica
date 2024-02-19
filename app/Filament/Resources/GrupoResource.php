<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Grupo;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GrupoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GrupoResource\RelationManagers;

class GrupoResource extends Resource
{
    protected static ?string $model = Grupo::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    // Modifica el nombre del label del panel
    //protected static ?string $navigationLabel = 'Ver citas';

    // Cambia tanto el nombre del label en el panel como en la información
    //protected static ?string $modelLabel = 'Mis citas';

    protected static ?string $navigationGroup = 'Organización';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles')
                    ->description('Ingrese información del grupo')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('nombre')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?String $state) => $set('slug', Str::slug($state)))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->suffixIcon('heroicon-o-globe-alt')
                            ->suffixIconColor('primary')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->icon('heroicon-o-user-group')
                    ->iconColor('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->icon('heroicon-o-globe-alt')
                    ->iconColor('primary'),
                TextColumn::make('empleados_count')->counts('empleados')
                    ->label('Cantidad de Empleados')
                    ->alignment(Alignment::Center)
                    ->placeholder('Sin empleados'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Última edición')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
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
            'index' => Pages\ListGrupos::route('/'),
            'create' => Pages\CreateGrupo::route('/create'),
            'edit' => Pages\EditGrupo::route('/{record}/edit'),
        ];
    }
}
