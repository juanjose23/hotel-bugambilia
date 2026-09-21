<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\BusinessLogic\Restaurante\Validaciones\ValidarTransicionPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Notifications\Restaurante\NotificadorRestaurante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class CargarPedidoACuenta
{
    public function __construct(
        private RegistrarDetalleCuenta $registrarDetalle,
        private NotificadorRestaurante $notificador,
        private RestauranteRepositorioInterface $repositorio,
        private ValidarTransicionPedido $validarTransicion,
    ) {}

    public function ejecutar(
        Pedido $pedido,
        Cuenta $cuenta,
        ?int $usuarioId = null,
    ): Cuenta {
        if (! $cuenta->estaAbierta()) {
            throw new DomainException('La cuenta seleccionada no está abierta.');
        }

        if ($pedido->cliente_id !== null && $cuenta->cliente_id !== null && (int) $pedido->cliente_id !== (int) $cuenta->cliente_id) {
            throw new DomainException('El cliente del pedido no coincide con el cliente de la cuenta seleccionada.');
        }

        $this->validarTransicion->puedeCargarACuenta($pedido);

        return DB::transaction(function () use ($pedido, $cuenta, $usuarioId): Cuenta {
            $this->repositorio->actualizarPedido($pedido, [
                'subtotal' => $this->subtotalPedido($pedido),
            ]);

            $this->registrarDetalle->ejecutar(
                cuenta: $cuenta,
                concepto: "Consumo Restaurante (Comanda #{$pedido->codigo})",
                precioUnitario: $this->subtotalPedido($pedido),
                cantidad: 1,
                origen: $pedido,
                creadorId: $usuarioId,
            );

            $this->repositorio->actualizarPedido($pedido, [
                'cuenta_id' => $cuenta->id,
                'estado' => EstadoPedido::CARGADO_A_HABITACION,
                'cerrado_en' => now(),
            ]);

            $this->notificador->pedidoCargadoACuenta($pedido, $cuenta);

            return $cuenta->refresh();
        });
    }

    private function subtotalPedido(Pedido $pedido): float
    {
        $pedido->loadMissing('items');

        $sum = $pedido->items->sum('subtotal');

        return is_numeric($sum) ? (float) $sum : 0.0;
    }
}
