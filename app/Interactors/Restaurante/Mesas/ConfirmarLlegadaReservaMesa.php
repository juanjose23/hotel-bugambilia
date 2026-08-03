<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Enums\Cuentas\MetodoPago;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Interactors\Cuentas\RegistrarPagoCuenta;
use App\Interactors\Restaurante\Cuentas\AbrirCuentaYConsumoRestaurante;
use App\Interactors\Restaurante\Pedidos\AbrirPedidoMesa;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Mesas\ObtenerReservasVigentesMesaQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ConfirmarLlegadaReservaMesa
{
    public function __construct(
        private AbrirPedidoMesa $abrirPedido,
        private EnviarPedidoACocina $enviarCocina,
        private RestauranteRepositorioInterface $repositorio,
        private ReservaRepositorioInterface $reservas,
        private ObtenerReservasVigentesMesaQuery $reservasVigentes,
        private ObtenerDatosPedidoFormQuery $datosPedido,
        private AbrirCuentaYConsumoRestaurante $abrirCuentaRestaurante,
        private RegistrarPagoCuenta $registrarPago,
        private CuentaRepositorioInterface $cuentas,
    ) {}

    /**
     * @throws Throwable
     */
    public function ejecutar(int $mesaId, ?int $meseroId = null): Pedido
    {
        $mesa = $this->repositorio->obtenerEspacioPorId($mesaId);

        if (! $mesa instanceof Espacio) {
            throw new DomainException('Mesa no encontrada.');
        }

        $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
        $reservaId = isset($meta['reserva_id']) && is_numeric($meta['reserva_id'])
            ? (int) $meta['reserva_id']
            : $this->reservasVigentes->paraMesa($mesaId)?->id;

        return DB::transaction(function () use ($mesa, $reservaId, $meseroId, $meta): Pedido {
            $reserva = $reservaId !== null ? $this->reservas->obtenerPorId($reservaId) : null;
            $clienteId = $reserva?->cliente_id;
            $nombreCliente = $reserva instanceof Reserva ? $reserva->nombre_cliente : (is_string($meta['nombre_cliente'] ?? null) ? $meta['nombre_cliente'] : null);
            $reservaMeta = $reserva instanceof Reserva && is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
            $platosPreordenados = is_array($meta['platos_preordenados'] ?? null)
                ? $meta['platos_preordenados']
                : $this->itemsPreordenDesdeMeta($reservaMeta);

            $itemsPreorden = $this->normalizarItemsPreorden($platosPreordenados);

            // 1. Marcar reservación como completada
            if ($reserva instanceof Reserva) {
                $this->reservas->actualizar($reserva, [
                    'estado' => EstadoReserva::CHECKED_IN,
                ]);
            }

            // 2. Abrir comanda en la mesa cargando los platillos pre-ordenados
            $pedido = $this->abrirPedido->ejecutar(
                mesa: $mesa,
                meseroId: $meseroId,
                clienteId: $clienteId,
                notas: $nombreCliente !== null ? "Reserva de $nombreCliente" : 'Reserva confirmada',
                items: $itemsPreorden,
            );

            // 3. Si existe abono previo registrado en la reserva, transferirlo como saldo abonado en la cuenta del pedido
            if ($reserva instanceof Reserva && (float) $reserva->total_pagado > 0) {
                $abonoPrevio = (float) $reserva->total_pagado;
                $cuentaComanda = $pedido->cuenta;
                if ($cuentaComanda === null) {
                    $resAbrir = $this->abrirCuentaRestaurante->ejecutar($pedido, $meseroId);
                    $cuentaComanda = $resAbrir['cuenta'];
                }
                if ($this->cuentas->sumaPagosAplicados($cuentaComanda) < $abonoPrevio) {
                    $this->registrarPago->ejecutar(
                        cuenta: $cuentaComanda,
                        metodoPago: MetodoPago::TRANSFERENCIA,
                        monto: $abonoPrevio,
                        referenciaTransaccion: "ABONO-RES-{$reserva->codigo_reserva}",
                        observaciones: "Abono de garantía transferido desde la reserva {$reserva->codigo_reserva}",
                    );
                }
            }

            // 3. Si se pre-ordenaron platillos, enviarlos inmediatamente a cocina (KDS)
            if ($this->repositorio->contarItemsDePedido($pedido) > 0) {
                $this->enviarCocina->ejecutar($pedido);
            }

            // 4. Cambiar estado de la mesa a Ocupado
            $metaLimpia = $meta;
            unset(
                $metaLimpia['reserva_id'],
                $metaLimpia['codigo_reserva'],
                $metaLimpia['platos_preordenados'],
                $metaLimpia['platos_preordenados_count']
            );

            $this->repositorio->actualizarEspacio($mesa, [
                'estado' => EstadoEspacio::Ocupado,
                'meta_datos' => $metaLimpia,
            ]);

            return $pedido->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<int, mixed>
     */
    private function itemsPreordenDesdeMeta(array $meta): array
    {
        $items = $meta['platos_preordenados'] ?? $meta['items_preorden'] ?? [];

        return is_array($items) ? $items : [];
    }

    /**
     * @return array<int, array{plato_id: int, cantidad: float, precio_unitario: float, observaciones?: string|null}>
     */
    private function normalizarItemsPreorden(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalizados = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! is_numeric($item['plato_id'] ?? null)) {
                continue;
            }

            $platoId = (int) $item['plato_id'];
            if ($platoId <= 0) {
                continue;
            }

            $precio = is_numeric($item['precio_unitario'] ?? null)
                ? (float) $item['precio_unitario']
                : (is_numeric($item['precio'] ?? null) ? (float) $item['precio'] : 0.0);

            if ($precio <= 0.0) {
                $precio = $this->datosPedido->precioActualDePlato($platoId) ?? 0.0;
            }

            $observaciones = is_string($item['observaciones'] ?? null) && trim($item['observaciones']) !== ''
                ? trim($item['observaciones'])
                : null;

            $normalizados[] = [
                'plato_id' => $platoId,
                'cantidad' => max(1.0, is_numeric($item['cantidad'] ?? null) ? (float) $item['cantidad'] : 1.0),
                'precio_unitario' => $precio,
                'observaciones' => $observaciones,
            ];
        }

        return $normalizados;
    }
}
