<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Interactors\Restaurante\Pedidos\AbrirPedidoMesa;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Mesas\BloquearLlegadaReservaMesaQuery;
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
        private CuentaRepositorioInterface $cuentas,
        private CambiarEstadoMesa $cambiarEstadoMesa,
        private BloquearLlegadaReservaMesaQuery $bloqueos,
    ) {}

    /**
     * @throws Throwable
     */
    public function ejecutar(int $mesaId, ?int $meseroId = null): Pedido
    {
        return DB::transaction(function () use ($mesaId, $meseroId): Pedido {
            $mesa = $this->bloqueos->mesa($mesaId);
            if (! in_array($mesa->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
                throw new DomainException('La mesa ya está ocupada o no permite confirmar otra llegada.');
            }
            $reservaVigente = $this->reservasVigentes->paraMesa($mesaId);
            if ($reservaVigente === null) {
                throw new DomainException('No existe una reserva vigente para confirmar en este horario.');
            }
            $reserva = $this->bloqueos->reserva($reservaVigente->id);
            if (! in_array($reserva->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA], true)) {
                throw new DomainException('La llegada de esta reserva ya fue confirmada o la reserva no está vigente.');
            }
            $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
            $clienteId = $reserva->cliente_id;
            $nombreCliente = $reserva->nombre_cliente;
            $preordenDatos = $reserva->ultimaEntradaBitacora('preorden');
            $platosPreordenados = is_array($meta['platos_preordenados'] ?? null)
                ? $meta['platos_preordenados']
                : ($preordenDatos['items'] ?? []);

            $itemsPreorden = $this->normalizarItemsPreorden($platosPreordenados);

            // 1. Marcar reservación como completada
            $this->reservas->actualizar($reserva, ['estado' => EstadoReserva::CHECKED_IN]);

            // 2. Abrir comanda en la mesa cargando los platillos pre-ordenados
            $pedido = $this->abrirPedido->ejecutar(
                mesa: $mesa,
                meseroId: $meseroId,
                clienteId: $clienteId,
                notas: $nombreCliente !== null ? "Reserva de $nombreCliente" : 'Reserva confirmada',
                items: $itemsPreorden,
            );

            // La reserva ya contiene los cargos de mesa, menú, descuentos y abonos.
            // La comanda conserva ese folio sin volver a ingresar dinero ni duplicar la preorden.
            $cuentaReserva = $this->cuentas->primeraCuentaDeReserva($reserva->id);
            if ($cuentaReserva === null) {
                throw new DomainException('La reserva no tiene una cuenta válida. Recepción debe revisarla antes de confirmar.');
            }
            $this->repositorio->actualizarPedido($pedido, ['cuenta_id' => $cuentaReserva->id]);
            $pedido->setRelation('cuenta', $cuentaReserva);
            $reserva->crearEntradaBitacora('llegada_restaurante', [
                'pedido_id' => $pedido->id,
                'mesa_id' => $mesa->id,
                'items_incluidos' => $pedido->items->pluck('id')->all(),
            ]);

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

            $mesaActualizada = $this->cambiarEstadoMesa->ejecutar($mesa->id, EstadoEspacio::Ocupado, MotivoTransicionMesa::LlegadaReserva);
            $this->repositorio->actualizarEspacio($mesaActualizada, ['meta_datos' => $metaLimpia]);

            return $pedido->refresh();
        });
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
