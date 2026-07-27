<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\BusinessLogic\Compras\VerificarEdicionCotizacion;
use App\BusinessLogic\Compras\VerificarSolicitudBloqueada;
use App\Enums\Compras\EstadoCotizacion;
use App\Events\Compras\GanadorSeleccionado;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Interactors\Compras\Cotizaciones\RechazarCotizacion;
use App\Interactors\Compras\OrdenesCompra\GenerarOrdenDesdeCotizacion;
use App\Repository\Models\Compras\Cotizacion;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewCotizacion extends ViewRecord
{
    protected GenerarOrdenDesdeCotizacion $generarOrdenDesdeCotizacion;

    public function boot(GenerarOrdenDesdeCotizacion $generarOrdenDesdeCotizacion): void
    {
        $this->generarOrdenDesdeCotizacion = $generarOrdenDesdeCotizacion;
    }

    protected static string $resource = CotizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (Cotizacion $record) => app(VerificarEdicionCotizacion::class)->puedeEditar($record)),

            DeleteAction::make()
                ->visible(fn (Cotizacion $record) => app(VerificarEdicionCotizacion::class)->puedeEditar($record)),

            Action::make('generarOrden')
                ->label('Generar Orden de Compra')
                ->icon(Heroicon::ShoppingCart)
                ->color('primary')
                ->visible(fn (Cotizacion $record) => ($record->es_elegida || $record->items()->where('es_elegido', true)->exists())
                    && $record->solicitud !== null
                    && ! app(VerificarSolicitudBloqueada::class)->estaBloqueada($record->solicitud)
                )
                ->requiresConfirmation()
                ->action(function (Cotizacion $record) {
                    try {
                        $orden = $this->generarOrdenDesdeCotizacion->ejecutar($record->id);

                        GanadorSeleccionado::dispatch($record);

                        Notification::make()
                            ->title('Orden de Compra Generada')
                            ->body("Se ha creado la orden $orden->codigo.")
                            ->success()
                            ->send();

                        return redirect(OrdenCompraResource::getUrl('edit', ['record' => $orden]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al generar la orden')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return \Illuminate\Log\log('error', (array) $e->getMessage());
                    }
                }),

            Action::make('rechazar')
                ->label('Rechazar')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechazar cotización')
                ->modalDescription('¿Está seguro de rechazar esta cotización?')
                ->schema([
                    Textarea::make('motivo')
                        ->label('Motivo de rechazo')
                        ->required()
                        ->placeholder('Indique la razón del rechazo...'),
                ])
                ->visible(fn (Cotizacion $record) => $record->estado === EstadoCotizacion::Activa)
                ->action(function (Cotizacion $record, array $data) {
                    try {
                        app(RechazarCotizacion::class)->ejecutar($record, $data['motivo']);

                        Notification::make()
                            ->title('Cotización Rechazada')
                            ->success()
                            ->send();
                    } catch (\DomainException $e) {

                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('imprimir')
                ->label('Imprimir')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn (Cotizacion $record) => route('admin.compras.reportes.cotizacion', $record))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('Compras:ImprimirCotizacion') ?? false),
        ];
    }
}
