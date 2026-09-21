<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\BusinessLogic\Espacios\ValidarEspacioOperativoConActivo;
use App\BusinessLogic\Restaurante\Mesas\ValidarTransicionMesa;
use App\BusinessLogic\Restaurante\Pedidos\AsignarClienteTemporal;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\AreaCocina;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class AbrirPedidoMesa
{
    public function __construct(
        private ValidarTransicionMesa $validarTransicion,
        private ValidarEspacioOperativoConActivo $validarOperativoConActivo,
        private RestauranteRepositorioInterface $repositorio,
        private AsignarClienteTemporal $asignarClienteTemporal,
        private RecalcularTotalesPedido $recalcular,
    ) {}

    /**
     * @param  array<int, array{
     *     tipo_item?: string,
     *     plato_id?: int|string|null,
     *     producto_id?: int|string|null,
     *     producto_variante_id?: int|string|null,
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
            if (! in_array($mesa->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
                throw new DomainException('La mesa no está disponible.');
            }

            $this->validarOperativoConActivo->validar($mesa);

            try {
                $motivo = $mesa->estado === EstadoEspacio::Reservado
                    ? MotivoTransicionMesa::LlegadaReserva
                    : MotivoTransicionMesa::AperturaPedido;
                $this->validarTransicion->validar($mesa->estado, EstadoEspacio::Ocupado, $motivo);
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
                $tipoItem = ($itemData['tipo_item'] ?? null) === 'producto' ? 'producto' : 'plato';
                $platoId = isset($itemData['plato_id']) && is_numeric($itemData['plato_id']) ? (int) $itemData['plato_id'] : null;
                $productoId = isset($itemData['producto_id']) && is_numeric($itemData['producto_id']) ? (int) $itemData['producto_id'] : null;
                $varianteId = isset($itemData['producto_variante_id']) && is_numeric($itemData['producto_variante_id']) ? (int) $itemData['producto_variante_id'] : null;

                if ($tipoItem === 'plato' && empty($platoId)) {
                    continue;
                }

                if ($tipoItem === 'producto' && empty($productoId)) {
                    continue;
                }

                $cant = is_numeric($itemData['cantidad'] ?? null) ? (float) $itemData['cantidad'] : 1.0;
                $precio = is_numeric($itemData['precio_unitario'] ?? null) ? (float) $itemData['precio_unitario'] : 0.0;
                $obs = is_string($itemData['observaciones'] ?? null) && $itemData['observaciones'] !== '' ? $itemData['observaciones'] : null;

                $areaCocina = AreaCocina::COCINA;
                if ($tipoItem === 'plato') {
                    $plato = Plato::query()->find($platoId);
                    if ($plato instanceof Plato && $plato->area_cocina instanceof AreaCocina) {
                        $areaCocina = $plato->area_cocina;
                    }
                } elseif ($tipoItem === 'producto') {
                    $areaCocina = AreaCocina::BAR;
                }

                $this->repositorio->crearPedidoItem([
                    'pedido_id' => $pedido->id,
                    'tipo_item' => $tipoItem,
                    'plato_id' => $platoId,
                    'producto_id' => $productoId,
                    'producto_variante_id' => $varianteId,
                    'area_cocina' => $areaCocina,
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
