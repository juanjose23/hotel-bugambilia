<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource\Pages;

use App\Enums\Facturacion\TipoFactura;
use App\Filament\Resources\Cuentas\VentaResource\VentaResource;
use App\Interactors\Ventas\RegistrarVentaDirecta;
use App\Repository\Models\Cuentas\Venta;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;

final class CreateVenta extends CreateRecord
{
    protected static string $resource = VentaResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Venta
    {
        $usuarioId = auth()->id();
        $clienteId = isset($data['cliente_id']) && is_numeric($data['cliente_id']) ? (int) $data['cliente_id'] : null;
        $monedaId = isset($data['moneda_id']) && is_numeric($data['moneda_id']) ? (int) $data['moneda_id'] : 1;

        /** @var array<int, array{concepto: string, cantidad: float|int, precio_unitario: float|int, descuento?: float|int|null}> $items */
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        $emitirFactura = (bool) ($data['emitir_factura'] ?? false);
        $facturaSerieId = isset($data['factura_serie_id']) && is_numeric($data['factura_serie_id']) ? (int) $data['factura_serie_id'] : null;
        $tipoFactura = isset($data['tipo_factura']) && is_numeric($data['tipo_factura'])
            ? TipoFactura::tryFrom((int) $data['tipo_factura'])
            : null;

        $datosFiscales = [
            'metodo_pago' => $data['metodo_pago'] ?? 'Efectivo',
        ];

        try {
            return app(RegistrarVentaDirecta::class)->ejecutar(
                clienteId: $clienteId,
                monedaId: $monedaId,
                items: $items,
                usuarioId: is_int($usuarioId) ? $usuarioId : null,
                emitirFactura: $emitirFactura,
                facturaSerieId: $facturaSerieId,
                tipoFactura: $tipoFactura,
                datosFiscales: $datosFiscales,
            );
        } catch (DomainException $e) {
            Notification::make()
                ->title('Error al registrar la venta')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
