<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListLimpiezaEjecuciones extends ListRecords
{
    protected static string $resource = LimpiezaEjecucionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'en_progreso' => Tab::make('En Progreso')
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoLimpieza::EnProgreso)),
            'pendiente' => Tab::make('Pendientes')
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoLimpieza::Pendiente)),
            'completada' => Tab::make('Completadas')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('estado', [
                    EstadoLimpieza::Completada,
                    EstadoLimpieza::CompletadaConDiscrepancia,
                ])),
        ];
    }
}
