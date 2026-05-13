<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Models\Compras\Solicitud;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ListCotizaciones extends ListRecords
{
    protected static string $resource = CotizacionResource::class;

    public function getTabs(): array
    {
        return [
            'listado' => Tab::make()
                ->label('Listado de Cotizaciones')
                ->icon(Heroicon::ListBullet),
            'solicitudes' => Tab::make()
                ->label('Solicitudes para Comparar')
                ->icon(Heroicon::DocumentMagnifyingGlass),
        ];
    }

    public function content(Schema $schema): Schema
    {
        if ($this->activeTab === 'solicitudes') {
            return $schema->components([
                $this->getTabsContentComponent(),
                View::make('filament.resources.compras.cotizaciones.tabs.solicitudes-resumen')
                    ->viewData([
                        'solicitudes' => Solicitud::withCount('cotizaciones')
                            ->where('estado', EstadoSolicitud::Aprobada->value)
                            ->limit(50)
                            ->get(),
                    ]),
            ]);
        }

        return parent::content($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nueva Cotización'),
        ];
    }
}
