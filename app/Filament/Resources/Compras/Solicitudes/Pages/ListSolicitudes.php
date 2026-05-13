<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ListSolicitudes extends ListRecords
{
    protected static string $resource = SolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'todos' => Tab::make()
                ->label('Todas')
                ->icon(Heroicon::ListBullet),

            'borrador' => Tab::make()
                ->label('Borrador')
                ->icon(Heroicon::DocumentText)
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoSolicitud::Borrador)),

            'aprobada' => Tab::make()
                ->label('Aprobadas')
                ->icon(Heroicon::CheckBadge)
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoSolicitud::Aprobada)),

            'cancelada' => Tab::make()
                ->label('Cancelada')
                ->icon(Heroicon::XCircle)
                ->modifyQueryUsing(fn ($query) => $query->where('estado', EstadoSolicitud::Cancelada)),

            'papelera' => Tab::make()
                ->label('Papelera')
                ->icon(Heroicon::Trash)
                ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
        ];
    }
}
