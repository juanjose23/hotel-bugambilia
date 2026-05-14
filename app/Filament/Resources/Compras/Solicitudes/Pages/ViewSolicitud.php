<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Models\Compras\Solicitud;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Spatie\LaravelPdf\Facades\Pdf;

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
                    $record->load(['colaborador.persona.personaNatural', 'departamentoSolicitante', 'items.producto', 'items.productoVariante', 'items.unidadMedida']);

                    $logoPath = public_path('img/logo-horizontal.png');
                    $logoBase64 = '';
                    if (file_exists($logoPath)) {
                        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($logoPath));
                    }

                    return Pdf::view('reports.compras.solicitud', [
                        'record' => $record,
                        'logo_base64' => $logoBase64,
                        'hotelInfo' => [
                            'telefono' => '+505 8713 6805',
                            'email' => 'recepcion@bugambiliashotel.com',
                            'direccion' => 'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, 1c. Oeste',
                        ],
                    ])->name("Solicitud-{$record->codigo}.pdf")->download();
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
