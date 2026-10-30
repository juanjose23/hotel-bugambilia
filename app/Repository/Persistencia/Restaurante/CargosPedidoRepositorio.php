<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Restaurante\PedidoItem;
use DomainException;

final class CargosPedidoRepositorio
{
    public function anular(PedidoItem $item): void
    {
        $detalles = CuentaDetalle::query()->where('origen_type', $item->getMorphClass())->where('origen_id', $item->id)->where('estado', EstadoGeneral::Activo->value)->lockForUpdate()->get();
        foreach ($detalles as $detalle) {
            $cuenta = Cuenta::query()->lockForUpdate()->findOrFail($detalle->cuenta_id);
            if (! $cuenta->estaAbierta() || (float) $cuenta->total_pagado > 0) {
                throw new DomainException('Esta partida tiene pagos o una cuenta cerrada; tramite primero el ajuste financiero autorizado.');
            }
            $detalle->update(['estado' => EstadoGeneral::Inactivo->value, 'anulado_en' => now(), 'descripcion' => trim(($detalle->descripcion ?? '').' Anulado desde pedido; el consumo físico se conserva.')]);
        }
    }
}
