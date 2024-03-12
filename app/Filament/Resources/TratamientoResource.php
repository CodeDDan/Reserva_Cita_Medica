<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TratamientoResource\Pages;
use App\Filament\Resources\TratamientoResource\RelationManagers;
use App\Models\Tratamiento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class TratamientoResource extends Resource
{
    protected static ?string $model = Tratamiento::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    // Modifica el nombre del label del panel
    //protected static ?string $navigationLabel = 'Ver citas';

    // Cambia tanto el nombre del label en el panel como en la información
    //protected static ?string $modelLabel = 'Mis citas';

    protected static ?string $navigationGroup = 'Gestión Clínica';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('diagnostico_id')
                    ->relationship('diagnostico', 'id')
                    ->getOptionLabelFromRecordUsing(function (Model $record) {
                        // Construiremos el query de selección con multiples detalles
                        // Dado que la relación está en diagnóstico, a partir de ahí nos movemos
                        $pacienteNombre = $record->cita->paciente->nombre_completo;
                        $empleadoNombre = $record->cita->empleado->nombre_completo;
                        $fechaAtencion = $record->cita->fecha_inicio_cita;
                        // Concatenar los nombres del paciente y el empleado.
                        return "{$pacienteNombre} - {$empleadoNombre} - {$fechaAtencion}";
                    })
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('tipo_tratamiento')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('medicamento')
                    ->maxLength(255),
                Forms\Components\TextInput::make('dosis')
                    ->maxLength(255),
                Forms\Components\Textarea::make('procedimiento')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('notas')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('diagnostico.id')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('diagnostico.cita.paciente.nombre_completo')
                    ->label('Paciente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('diagnostico.cita.empleado.nombre_completo')
                    ->label('Doctor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_tratamiento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('medicamento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dosis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notas')
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
            'index' => Pages\ListTratamientos::route('/'),
            'create' => Pages\CreateTratamiento::route('/create'),
            'edit' => Pages\EditTratamiento::route('/{record}/edit'),
        ];
    }
}
