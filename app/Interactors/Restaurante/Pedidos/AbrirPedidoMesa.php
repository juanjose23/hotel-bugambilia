<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\BusinessLogic\Restaurante\Mesas\ValidarTransicionMesa;
use App\BusinessLogic\Restaurante\Pedidos\AsignarClienteTemporal;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AbrirPedidoMesa
{
    public function __construct(
        private readonly ValidarTransicionMesa $validarTransicion,
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly AsignarClienteTemporal $asignarClienteTemporal,
        private readonly RecalcularTotalesPedido $recalcular,
    ) {}

    /**
     * @param  array<int, array{
     *     plato_id: int|string,
     *     cantidad?: float|int|string,
     *     precio_unitario?: float|int|string,
     *     observaciones?: string|null
     * }>  $items
     */
    public function ejecutar(
        ?Espacio $mesa = null,
        ?int $meseroId = null,
        ?int $clienteId = null,
        ?string $notas = null,
        array $items = [],
    ): Pedido {
        if ($mesa instanceof Espacio) {
            if ($mesa->estado !== EstadoEspacio::Disponible) {
                throw new DomainException('La mesa no está disponible.');
            }

            try {
                $this->validarTransicion->validar($mesa->estado, EstadoEspacio::Ocupado, MotivoTransicionMesa::AperturaPedido);
            } catch (DomainException) {
                throw new DomainException('La mesa no está disponible.');
            }
        }

        $notasResueltas = $notas;
        if ($clienteId === null && empty($notasResueltas)) {
            $clienteTemporal = $this->asignarClienteTemporal->resolverNombreCliente($mesa, null);
            $notasResueltas = "Atención: {$clienteTemporal}";
        }

        return DB::transaction(function () use ($mesa, $meseroId, $clienteId, $notasResueltas, $items): Pedido {
            $codigo = 'PED-'.date('Ymd').'-'.strtoupper(Str::random(6));

            $cuentaExistenteId = null;
            if ($mesa instanceof Espacio) {
                $cuentaExistenteId = $this->repositorio->obtenerCuentaIdDePedidoActivoEnMesa($mesa->id);
            }

            $pedido = new Pedido([
                'codigo' => $codigo,
                'mesa_id' => $mesa?->id,
                'mesero_id' => $meseroId,
                'cliente_id' => $clienteId,
                'cuenta_id' => $cuentaExistenteId,
                'estado' => EstadoPedido::ABIERTO,
                'subtotal' => 0.00,
                'consecutivo_comanda' => 1,
                'abierto_en' => now(),
                'notas' => $notasResueltas,
            ]);

            $this->repositorio->guardarPedido($pedido);

            foreach ($items as $itemData) {
                if (empty($itemData['plato_id'])) {
                    continue;
                }

                $platoId = (int) $itemData['plato_id'];
                if ($platoId <= 0) {
                    continue;
                }

                $cant = is_numeric($itemData['cantidad'] ?? null) ? (float) $itemData['cantidad'] : 1.0;
                $precio = is_numeric($itemData['precio_unitario'] ?? null) ? (float) $itemData['precio_unitario'] : 0.0;
                $obs = is_string($itemData['observaciones'] ?? null) && $itemData['observaciones'] !== '' ? $itemData['observaciones'] : null;

                $this->repositorio->crearPedidoItem([
                    'pedido_id' => $pedido->id,
                    'plato_id' => $platoId,
                    'cantidad' => $cant,
                    'precio_unitario' => $precio,
                    'subtotal' => round($precio * $cant, 2),
                    'observaciones' => $obs,
                    'estado' => EstadoItemPedido::PENDIENTE,
                ]);
            }

            if ($mesa instanceof Espacio) {
                $this->repositorio->actualizarEspacio($mesa, [
                    'estado' => EstadoEspacio::Ocupado,
                ]);
            }

            if ($this->repositorio->contarItemsDePedido($pedido) > 0) {
                $this->recalcular->ejecutar($pedido);
            }

            return $pedido->refresh();
        });
    }
}
