<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Enums\Reservas\EstadoReserva;
use App\Filament\Resources\Reservas\ReservaResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ListReservas extends ListRecords
{
    protected static string $resource = ReservaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calendario')
                ->label('Ver Calendario')
                ->icon(Heroicon::CalendarDays)
                ->color('info')
                ->url('/admin/reservas/calendario'),
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas'),
            'pendientes' => Tab::make('Pendientes')
                ->badge(EstadoReserva::PENDIENTE->getLabel())
                ->badgeColor(EstadoReserva::PENDIENTE->getColor())
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoReserva::PENDIENTE->value)),
            'confirmadas' => Tab::make('Confirmadas')
                ->badge(EstadoReserva::CONFIRMADA->getLabel())
                ->badgeColor(EstadoReserva::CONFIRMADA->getColor())
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoReserva::CONFIRMADA->value)),
            'checked_in' => Tab::make('En estancia')
                ->badge(EstadoReserva::CHECKED_IN->getLabel())
                ->badgeColor(EstadoReserva::CHECKED_IN->getColor())
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoReserva::CHECKED_IN->value)),
            'checked_out' => Tab::make('Completadas')
                ->badge(EstadoReserva::CHECKED_OUT->getLabel())
                ->badgeColor(EstadoReserva::CHECKED_OUT->getColor())
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoReserva::CHECKED_OUT->value)),
            'canceladas' => Tab::make('Canceladas')
                ->badge(EstadoReserva::CANCELADA->getLabel())
                ->badgeColor(EstadoReserva::CANCELADA->getColor())
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoReserva::CANCELADA->value)),
        ];
    }

    public function getDefaultActiveTab(): ?string
    {
        return 'all';
    }
}
