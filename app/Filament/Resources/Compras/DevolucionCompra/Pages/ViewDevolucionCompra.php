<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Pages;

use App\Enums\Compras\EstadoDevolucion;
use App\Filament\Resources\Compras\DevolucionCompra\DevolucionCompraResource;
use App\Interactors\Compras\Devoluciones\DevolverMercanciaProveedor;
use App\Repository\Models\Compras\DevolucionCompra;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

/**
 * @property DevolucionCompra $record
 */
class ViewDevolucionCompra extends ViewRecord
{
    protected DevolverMercanciaProveedor $devolverMercanciaProveedor;

    public function boot(DevolverMercanciaProveedor $devolverMercanciaProveedor): void
    {
        $this->devolverMercanciaProveedor = $devolverMercanciaProveedor;
    }

    protected static string $resource = DevolucionCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir')
                ->label('Imprimir')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn (DevolucionCompra $record) => route('reporte.devolucion', $record))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('Compras:ImprimirDevolucion') ?? false),

            EditAction::make()
                ->visible(fn () => $this->record->estado !== EstadoDevolucion::Confirmada),

            Action::make('confirmar')
                ->label('Confirmar Devolución')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Devolución al Proveedor')
                ->modalDescription('Al confirmar, se retirará el stock físico del inventario (registrando movimientos de tipo DEVOLUCION_PROVEEDOR) y se liberará el saldo de la Orden de Compra para futuras recepciones. Esta acción no se puede deshacer.')
                ->action(function () {
                    try {
                        $this->devolverMercanciaProveedor->ejecutar($this->record, (int) auth()->id());

                        Notification::make()
                            ->title('Devolución Confirmada')
                            ->body("La devolución {$this->record->codigo} ha sido procesada de manera exitosa. El stock físico ha sido descontado y el saldo del PO ha sido liberado.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['estado']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Error al procesar devolución')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->estado !== EstadoDevolucion::Confirmada),
        ];
    }
}
