<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Validacion;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\Stock;

class ValidacionLotes
{
    public function validarCambioSubUbicacion(Lote $lote, int $ubicacionDetalleId): void
    {
        if ((int) $lote->ubicacion_detalle_id === $ubicacionDetalleId) {
            throw new \RuntimeException('El lote ya está asignado a esta sub-ubicación.');
        }
    }

    public function validarNoEnCuarentena(Lote $lote): void
    {
        if ($lote->estado === EstadoLote::Cuarentena) {
            throw new \RuntimeException('El lote ya se encuentra en cuarentena.');
        }
    }

    public function validarCantidadMerma(float $cantidad, Lote $lote): void
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad de merma debe ser mayor a cero.');
        }

        if ($lote->cantidad_disponible < $cantidad) {
            throw new \RuntimeException(sprintf(
                'Stock insuficiente para merma. Disponible: %s, Solicitado: %s',
                $lote->cantidad_disponible,
                $cantidad,
            ));
        }
    }

    public function validarTraslado(int $origenId, int $destinoId): void
    {
        if ($origenId === $destinoId) {
            throw new \InvalidArgumentException('La ubicación origen y destino no pueden ser la misma.');
        }
    }

    public function validarStockSuficiente(Stock $stock, Lote $lote): void
    {
        if ($stock->cantidad < $lote->cantidad_disponible) {
            throw new \RuntimeException(sprintf(
                'Stock insuficiente en origen. Disponible en stock: %s, Cantidad del lote: %s',
                $stock->cantidad,
                $lote->cantidad_disponible,
            ));
        }
    }

    public function determinarEstadoPorStock(Lote $lote): EstadoLote
    {
        if ($lote->cantidad_disponible > 0) {
            return $lote->estado;
        }

        return EstadoLote::Rechazado;
    }
}
