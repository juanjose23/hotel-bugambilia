<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Actions\Compras\GenerarReporteSolicitudPdfAction;
use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Models\Compras\Solicitud;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewSolicitud extends ViewRecord
{
    protected static string $resource = SolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('PDF')
                ->icon(Heroicon::DocumentArrowDown)
                ->color('danger')
                ->action(function () {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();
                    $action = new GenerarReporteSolicitudPdfAction;
                    $pdf = $action->ejecutar($record);

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        "Solicitud-{$record->codigo}.pdf"
                    );
                }),

            EditAction::make()
                ->visible(function (): bool {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return $record->estado === EstadoSolicitud::Borrador;
                }),
        ];
    }
}
