<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Validaciones;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;

final class ValidarTransicionPedido
{
    /**
     * @var EstadoPedido[]
     */
    private const array ESTADOS_TERMINALES = [
        EstadoPedido::PAGADO,
        EstadoPedido::CARGADO_A_HABITACION,
        EstadoPedido::CANCELADO,
    ];

    public function esEstadoTerminal(EstadoPedido $estado): bool
    {
        return in_array($estado, self::ESTADOS_TERMINALES, true);
    }

    public function puedeCancelar(Pedido $pedido): void
    {
        if ($this->esEstadoTerminal($pedido->estado)) {
            throw new DomainException(
                "El pedido #{$pedido->codigo} no puede ser cancelado (estado: {$pedido->estado->getLabel()})."
            );
        }
    }

    public function puedeSeparar(Pedido $pedido): void
    {
        $noPermitidos = [
            EstadoPedido::PAGADO,
            EstadoPedido::LISTO,
            EstadoPedido::SERVIDO,
            EstadoPedido::CANCELADO,
        ];

        if (in_array($pedido->estado, $noPermitidos, true)) {
            throw new DomainException('No se puede dividir un pedido en estado terminal.');
        }
    }

    public function puedeCargarACuenta(Pedido $pedido): void
    {
        if ($pedido->estado === EstadoPedido::CARGADO_A_HABITACION) {
            throw new DomainException("El pedido #{$pedido->codigo} ya fue cargado a una cuenta.");
        }

        if (in_array($pedido->estado, [EstadoPedido::LISTO, EstadoPedido::SERVIDO, EstadoPedido::CANCELADO, EstadoPedido::PAGADO], true)) {
            throw new DomainException("El pedido #{$pedido->codigo} ya no permite cambios.");
        }
    }

    public function puedeCerrar(Pedido $pedido): void
    {
        if (in_array($pedido->estado, [EstadoPedido::PAGADO, EstadoPedido::CARGADO_A_HABITACION], true)) {
            throw new DomainException("El pedido #{$pedido->codigo} ya fue cerrado previamente.");
        }

        if ($pedido->estado === EstadoPedido::CANCELADO) {
            throw new DomainException("El pedido #{$pedido->codigo} no puede cerrarse porque está cancelado.");
        }
    }

    public function puedeModificarItems(Pedido $pedido): void
    {
        if ($this->esEstadoTerminal($pedido->estado)) {
            throw new DomainException(
                "No se pueden modificar los ítems del pedido #{$pedido->codigo} porque está en estado '{$pedido->estado->getLabel()}'."
            );
        }
    }
}
