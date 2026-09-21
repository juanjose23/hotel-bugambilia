<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaResource\Pages;

use App\Enums\Facturacion\EstadoFactura;
use App\Filament\Resources\Cuentas\VentaResource\VentaResource;
use App\Filament\Resources\Facturacion\FacturaResource\FacturaResource;
use App\Interactors\Facturacion\AnularFacturaFiscal;
use App\Repository\Models\Facturacion\Factura;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

final class ViewFactura extends ViewRecord
{
    protected static string $resource = FacturaResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Factura $factura */
        $factura = $this->getRecord();

        return [
            // Imprimir Factura Fiscal
            Action::make('imprimir')
                ->label('Imprimir Factura')
                ->icon(Heroicon::Printer)
                ->color('success')
                ->url(fn (): string => route('admin.facturacion.facturas.imprimir', ['factura' => $factura]))
                ->openUrlInNewTab(),

            // Descargar PDF
            Action::make('pdf')
                ->label('Descargar PDF')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->url(fn (): string => route('admin.facturacion.facturas.pdf', ['factura' => $factura]))
                ->openUrlInNewTab(),

            // Ver Venta asociada
            Action::make('ver_venta')
                ->label('Ver Venta Asociada')
                ->icon(Heroicon::DocumentCurrencyDollar)
                ->color('info')
                ->visible(fn (): bool => $factura->venta_id !== null)
                ->url(fn (): string => $factura->venta_id ? VentaResource::getUrl('view', ['record' => $factura->venta_id]) : '#'),

            // Anular Factura Fiscal
            Action::make('anular_factura')
                ->label('Anular Factura Fiscal DGI')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn (): bool => $factura->estado === EstadoFactura::Emitida)
                ->requiresConfirmation()
                ->modalHeading('¿Anular esta Factura Fiscal ante la DGI?')
                ->modalDescription('Esta acción anulará el folio fiscal en el sistema y registrará el motivo oficial en auditoría.')
                ->schema([
                    Textarea::make('motivo')
                        ->label('Motivo fiscal de la anulación')
                        ->placeholder('Ej: Error en datos del receptor, devolución de mercadería...')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    /** @var Factura $factura */
                    $factura = $this->getRecord();
                    $usuarioId = auth()->id();

                    try {
                        app(AnularFacturaFiscal::class)->ejecutar(
                            factura: $factura,
                            motivo: (string) $data['motivo'],
                            usuarioId: is_int($usuarioId) ? $usuarioId : null,
                        );

                        Notification::make()
                            ->title('Factura Anulada')
                            ->body("La factura oficial N° {$factura->numero} ha sido anulada con éxito.")
                            ->warning()
                            ->send();

                        $this->refreshFormData(['estado', 'anulada_en']);
                    } catch (DomainException $e) {
                        Notification::make()
                            ->title('Error al anular factura')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
