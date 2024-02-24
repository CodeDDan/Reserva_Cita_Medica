<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Diagnostico;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DiagnosticoResource\Pages;
use App\Filament\Resources\DiagnosticoResource\RelationManagers;

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
                    ->getOptionLabelFromRecordUsing(function (Model $record) {
                        // Aquí asumo que 'paciente' y 'empleado' son atributos en tu modelo 'cita'.
                        $pacienteNombre = $record->paciente->nombre_completo; // Ajusta según la estructura de tu modelo.
                        $empleadoNombre = $record->empleado->nombre_completo; // Ajusta según la estructura de tu modelo.

                        // Concatenar los nombres del paciente y el empleado.
                        return "{$pacienteNombre} - {$empleadoNombre}";
                    })
                    ->required(),
                Forms\Components\RichEditor::make('detalles')
                    ->required()
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
                Tables\Columns\TextColumn::make('cita.paciente.nombre_completo')
                    ->label('Paciente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cita.empleado.nombre_completo')
                    ->label('Doctor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cita.fecha_inicio_cita')
                    ->label('Fecha de la Cita')
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
