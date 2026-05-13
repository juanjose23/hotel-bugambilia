<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Models\Compras\Cotizacion;
use App\UseCases\Compras\GenerarOrdenDesdeCotizacion;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewCotizacion extends ViewRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generarOrden')
                ->label('Generar Orden de Compra')
                ->icon(Heroicon::ShoppingCart)
                ->color('primary')
                ->visible(fn (Cotizacion $record) => ($record->es_elegida || $record->items()->where('es_elegido', true)->exists())
                    && ! $record->solicitud->ordenesCompra()->where('proveedor_id', $record->proveedor_id)->exists()
                )
                ->requiresConfirmation()
                ->action(function (Cotizacion $record) {
                    try {
                        $orden = app(GenerarOrdenDesdeCotizacion::class)->execute($record->id);

                        Notification::make()
                            ->title('Orden de Compra Generada')
                            ->body("Se ha creado la orden {$orden->codigo}.")
                            ->success()
                            ->send();

                        return redirect(OrdenCompraResource::getUrl('edit', ['record' => $orden]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al generar la orden')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('imprimir')
                ->label('Imprimir')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn (Cotizacion $record) => route('reporte.cotizacion', $record))
                ->openUrlInNewTab(),
        ];
    }
}
