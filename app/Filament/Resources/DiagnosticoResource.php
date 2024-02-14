<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiagnosticoResource\Pages;
use App\Filament\Resources\DiagnosticoResource\RelationManagers;
use App\Models\Diagnostico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiagnosticoResource extends Resource
{
    protected static ?string $model = Diagnostico::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // Modifica el nombre del label del panel
    //protected static ?string $navigationLabel = 'Ver citas';

    // Cambia tanto el nombre del label en el panel como en la información
    //protected static ?string $modelLabel = 'Mis citas';

    protected static ?string $navigationGroup = 'Gestión Clínica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cita_id')
                    ->relationship('cita', 'id')
                    ->required(),
                Forms\Components\Textarea::make('detalles')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('examenes')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('observaciones')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cita.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('examenes')
                    ->searchable(),
                Tables\Columns\TextColumn::make('observaciones')
                    ->searchable(),
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
            'index' => Pages\ListDiagnosticos::route('/'),
            'create' => Pages\CreateDiagnostico::route('/create'),
            'edit' => Pages\EditDiagnostico::route('/{record}/edit'),
        ];
    }
}
