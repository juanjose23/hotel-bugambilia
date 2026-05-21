<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Models\Compras\Solicitud;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewSolicitud extends ViewRecord
{
    protected static string $resource = SolicitudResource::class;

    public function getRecord(): Solicitud
    {
        /** @var Solicitud $record */
        $record = parent::getRecord();
        $record->loadMissing('items.productoVariante');

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crearCotizacion')
                ->label('Crear Cotización')
                ->icon(Heroicon::ClipboardDocumentCheck)
                ->color('warning')
                ->url(fn (Solicitud $record) => CotizacionResource::getUrl('create', [
                    'solicitud_id' => $record->id,
                ]))
                ->visible(fn (Solicitud $record) => $record->estado === EstadoSolicitud::Aprobada),

            Action::make('imprimir')
                ->label('Imprimir')
                ->icon(Heroicon::Printer)
                ->color('info')
                ->url(fn (Solicitud $record) => route('reporte.solicitud', $record))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()->can('Compras:ImprimirSolicitud')),
            EditAction::make()
                ->visible(function (): bool {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return $record->estado === EstadoSolicitud::Borrador;
                }),
        ];
    }
}
