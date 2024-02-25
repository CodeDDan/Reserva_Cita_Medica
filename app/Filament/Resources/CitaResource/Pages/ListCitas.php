<?php

namespace App\Filament\Resources\CitaResource\Pages;

use Carbon\Carbon;
use App\Models\Cita;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\CitaResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCitas extends ListRecords
{
    protected static string $resource = CitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $fechaActual = Carbon::now()->toDateString();
        return [
            'Todas' => Tab::make()
                ->badge(Cita::count())
                ->icon('heroicon-m-wallet'),
            'Activas' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) use ($fechaActual) {
                    $query->whereNotIn('estado', ['Cancelado', 'Abandonado'])
                        ->whereDate('fecha_inicio_cita', '>=', $fechaActual);
                })
                ->badge(
                    Cita::whereNotIn('estado', ['Cancelado', 'Abandonado'])
                        ->whereDate('fecha_inicio_cita', '>=', $fechaActual)
                        ->count()
                )
                ->icon('heroicon-m-user-group'),
            'Atendidas' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) use ($fechaActual) {
                    $query->whereNotIn('estado', ['Cancelado', 'Abandonado'])
                        ->whereDate('fecha_inicio_cita', '<', $fechaActual);
                })
                ->badge(
                    Cita::whereNotIn('estado', ['Cancelado', 'Abandonado'])
                        ->whereDate('fecha_inicio_cita', '<', $fechaActual)
                        ->count()
                )
                ->icon('heroicon-m-clipboard-document-list'),
            'Descartadas' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereIn('estado', ['Cancelado', 'Abandonado']);
                })
                ->badge(Cita::whereIn('estado', ['Cancelado', 'Abandonado'])->count())
                ->icon('heroicon-m-exclamation-circle'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'Activas';
    }
}
