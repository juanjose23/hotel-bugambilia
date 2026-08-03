<?php

declare(strict_types=1);

namespace App\Notifications\Restaurante;

use App\Notifications\NotificadorBase;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;

final class NotificadorRestaurante extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosRestaurante $destinatarios,
        private readonly MensajesRestaurante $mensajes,
    ) {}

    public function pedidoEnviadoACocina(Pedido $pedido): void
    {
        $usuarios = $this->destinatarios->obtenerCocina(
            $pedido->mesero?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->pedidoEnviadoACocina($pedido));
    }

    public function pedidoCancelado(Pedido $pedido): void
    {
        $usuarios = $this->destinatarios->obtenerTodos(
            $pedido->mesero?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->pedidoCancelado($pedido));
    }

    public function pedidoCargadoACuenta(Pedido $pedido, Cuenta $cuenta): void
    {
        $usuarios = $this->destinatarios->obtenerTodos(
            $pedido->mesero?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->pedidoCargadoACuenta($pedido, $cuenta));
    }

    public function stockMinimo(string $producto, float $cantidad, string $ubicacion): void
    {
        $usuarios = $this->destinatarios->obtenerCocina();

        $this->enviar($usuarios, $this->mensajes->stockMinimoRestaurante($producto, $cantidad, $ubicacion));
    }

    public function mesaUnida(string $mesaPrincipal, string $mesasSecundarias): void
    {
        $usuarios = $this->destinatarios->obtenerTodos();

        $this->enviar($usuarios, $this->mensajes->mesaUnida($mesaPrincipal, $mesasSecundarias));
    }

    public function mesaSeparada(string $mesa): void
    {
        $usuarios = $this->destinatarios->obtenerTodos();

        $this->enviar($usuarios, $this->mensajes->mesaSeparada($mesa));
    }
}
