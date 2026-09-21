<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource\Pages;

use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\TipoFactura;
use App\Filament\Resources\Cuentas\VentaResource\VentaResource;
use App\Filament\Resources\Facturacion\FacturaResource\FacturaResource;
use App\Interactors\Facturacion\EmitirFacturaDesdeVenta;
use App\Interactors\Ventas\AnularVenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Facturacion\FacturaSerie;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

final class ViewVenta extends ViewRecord
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Venta $venta */
        $venta = $this->getRecord();
        $venta->loadMissing(['facturas.serie', 'detalles', 'cliente.persona', 'moneda']);
        $facturaEmitida = $venta->facturas->first(fn ($f) => $f->estado === EstadoFactura::Emitida);

        return [
            // Ver Factura si ya existe emitida
            Action::make('ver_factura')
                ->label('Ver Factura Fiscal')
                ->icon(Heroicon::DocumentText)
                ->color('info')
                ->visible(fn (): bool => $facturaEmitida !== null)
                ->url(fn (): string => $facturaEmitida ? FacturaResource::getUrl('view', ['record' => $facturaEmitida->id]) : '#'),

            // Imprimir Factura Fiscal si ya existe emitida
            Action::make('imprimir_factura')
                ->label('Imprimir Factura')
                ->icon(Heroicon::Printer)
                ->color('success')
                ->visible(fn (): bool => $facturaEmitida !== null)
                ->url(fn (): string => $facturaEmitida ? route('admin.facturacion.facturas.imprimir', $facturaEmitida) : '#')
                ->openUrlInNewTab(),

            // Emitir Factura si la venta está emitida y aún no tiene factura emitida
            Action::make('emitir_factura')
                ->label('Emitir Factura Fiscal')
                ->icon(Heroicon::DocumentCurrencyDollar)
                ->color('success')
                ->visible(fn (): bool => $venta->estado === EstadoVenta::Emitida && $facturaEmitida === null)
                ->modalHeading('Emitir Factura Fiscal DGI')
                ->modalDescription('Seleccione la serie fiscal y tipo de factura para emitir el documento tributario oficial.')
                ->schema([
                    Select::make('factura_serie_id')
                        ->label('Serie Fiscal DGI')
                        ->options(fn () => FacturaSerie::query()->where('activa', true)->pluck('codigo', 'id'))
                        ->required()
                        ->searchable(),
                    Select::make('tipo')
                        ->label('Tipo de Factura')
                        ->options([
                            TipoFactura::Contado->value => TipoFactura::Contado->getLabel(),
                            TipoFactura::Credito->value => TipoFactura::Credito->getLabel(),
                        ])
                        ->default(TipoFactura::Contado->value)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    /** @var Venta $venta */
                    $venta = $this->getRecord();
                    $usuarioId = auth()->id();

                    try {
                        $factura = app(EmitirFacturaDesdeVenta::class)->ejecutar(
                            venta: $venta,
                            serieId: (int) $data['factura_serie_id'],
                            tipo: TipoFactura::from((int) $data['tipo']),
                            datosReceptor: [],
                            usuarioId: is_int($usuarioId) ? $usuarioId : null,
                        );

                        Notification::make()
                            ->title('Factura Emitida')
                            ->body("Se ha emitido la factura oficial N° {$factura->numero}.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['facturas']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al emitir factura')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Anular Venta
            Action::make('anular_venta')
                ->label('Anular Venta')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn (): bool => $venta->estado === EstadoVenta::Emitida)
                ->requiresConfirmation()
                ->modalHeading('¿Anular esta venta?')
                ->modalDescription('Al anular la venta, si existen facturas fiscales emitidas asociadas, también serán anuladas ante la DGI.')
                ->schema([
                    Textarea::make('motivo')
                        ->label('Motivo de la anulación')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    /** @var Venta $venta */
                    $venta = $this->getRecord();
                    $usuarioId = auth()->id();

                    try {
                        app(AnularVenta::class)->ejecutar(
                            venta: $venta,
                            motivo: (string) $data['motivo'],
                            usuarioId: is_int($usuarioId) ? $usuarioId : null,
                        );

                        Notification::make()
                            ->title('Venta Anulada')
                            ->body("La venta {$venta->numero_venta} ha sido marcada como anulada.")
                            ->warning()
                            ->send();

                        $this->refreshFormData(['estado', 'anulada_en']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al anular venta')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
