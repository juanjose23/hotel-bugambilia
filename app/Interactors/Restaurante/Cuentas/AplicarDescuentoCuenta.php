<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cuentas;

use App\BusinessLogic\Restaurante\Auditoria\RegistrarAuditoriaRestaurante;
use App\BusinessLogic\Restaurante\Cuentas\CalcularTotalesCuenta;
use App\Enums\Restaurante\AccionAuditoriaRestaurante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AplicarDescuentoCuenta
{
    public function __construct(
        private readonly CalcularTotalesCuenta $calcularTotales,
        private readonly RegistrarAuditoriaRestaurante $auditoria,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @throws \Throwable
     */
    public function ejecutar(
        int $pedidoId,
        float $descuentoPorcentaje = 0.0,
        float $descuentoMonto = 0.0,
        ?string $motivo = null
    ): Pedido {
        if ($descuentoPorcentaje < 0 || $descuentoPorcentaje > 100) {
            throw new DomainException('El porcentaje de descuento debe estar entre 0% y 100%.');
        }

        if ($descuentoMonto < 0) {
            throw new DomainException('El monto de descuento no puede ser negativo.');
        }

        return DB::transaction(function () use ($pedidoId, $descuentoPorcentaje, $descuentoMonto, $motivo): Pedido {
            $pedido = $this->repositorio->obtenerPedidoPorId($pedidoId);

            if (! $pedido instanceof Pedido) {
                throw new DomainException("Pedido #{$pedidoId} no encontrado.");
            }

            $pedido->descuento_porcentaje = $descuentoPorcentaje;
            $pedido->descuento_monto = $descuentoMonto;
            $pedido->motivo_descuento = $motivo;

            $pedido->loadMissing('items');
            $totales = $this->calcularTotales->calcular($pedido);
            $pedido->total = $totales['total'];
            $this->repositorio->guardarPedido($pedido);

            $this->auditoria->registrar(
                accion: AccionAuditoriaRestaurante::AplicarDescuento,
                mesaId: $pedido->mesa_id,
                pedidoId: $pedido->id,
                detalles: [
                    'descuento_porcentaje' => $descuentoPorcentaje,
                    'descuento_monto' => $descuentoMonto,
                    'motivo' => $motivo,
                    'nuevo_total' => $totales['total'],
                ]
            );

            return $pedido;
        });
    }
}
