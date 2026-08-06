<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\BusinessLogic\Cuentas\ValidarCreditoHabitacion;
use App\Enums\Cuentas\CategoriaConsumo;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Transfiere una comanda completa como un único detalle de cuenta.
 * Los productos permanecen disponibles en metadatos para conservar trazabilidad.
 */
final readonly class TransferirPedidoACuenta
{
    public function __construct(
        private RecalcularCuenta $recalcularCuenta,
        private CuentaRepositorioInterface $cuentas,
        private ValidarCreditoHabitacion $validarCredito,
    ) {}

    /**
     * @return CuentaDetalle[]
     *
     * @throws Throwable
     */
    public function ejecutar(Pedido $pedido, Cuenta $cuenta, ?int $usuarioId = null): array
    {
        if (! $cuenta->permiteNuevosCargos()) {
            throw new DomainException("La cuenta $cuenta->numero_cuenta no acepta nuevos cargos.");
        }

        if ($pedido->cliente_id !== null && $cuenta->cliente_id !== null && (int) $pedido->cliente_id !== (int) $cuenta->cliente_id) {
            throw new DomainException('El cliente del pedido no coincide con el cliente de la cuenta seleccionada.');
        }

        $pedido = $this->cuentas->pedidoConItemsCargados($pedido);

        $itemsActivos = $pedido->items->filter(fn ($item): bool => $item->estado !== EstadoItemPedido::ANULADO);
        $totalPedidoVal = $itemsActivos->sum('subtotal');
        $montoPedido = round(is_numeric($totalPedidoVal) ? (float) $totalPedidoVal : 0.0, 2);

        if ($cuenta->tipo_cuenta === TipoCuenta::ESTANCIA || $cuenta->estancia_id !== null) {
            $cuenta->loadMissing('estancia.reserva');
            $this->validarCredito->validar($cuenta->estancia, $cuenta, $montoPedido);
        }

        $detalles = [];

        DB::transaction(function () use ($pedido, $cuenta, $usuarioId, &$detalles): void {
            $pedidoKey = $pedido->getKey();
            $origenId = is_numeric($pedidoKey) ? (int) $pedidoKey : 0;

            $detalleExistente = $this->cuentas->detalleActivoConOrigen(
                $cuenta,
                (string) $pedido->getMorphClass(),
                $origenId,
            );

            /** @var Collection<int, array{item_id: int, plato_id: int, nombre: string, cantidad: float, precio_unitario: float, subtotal: float, observaciones: ?string, area_cocina: ?string}> $items */
            $items = $pedido->items
                ->filter(fn ($item): bool => $item->estado !== EstadoItemPedido::ANULADO)
                ->map(fn ($item): array => [
                    'item_id' => (int) $item->id,
                    'plato_id' => (int) $item->plato_id,
                    'nombre' => (string) ($item->plato->nombre ?? 'Platillo'),
                    'cantidad' => (float) $item->cantidad,
                    'precio_unitario' => (float) $item->precio_unitario,
                    'subtotal' => (float) $item->subtotal,
                    'observaciones' => $item->observaciones,
                    'area_cocina' => $item->area_cocina?->value,
                ]);

            $itemsValues = array_values($items->all());

            $sumSubtotal = $items->sum('subtotal');
            $totalPedido = round(is_numeric($sumSubtotal) ? (float) $sumSubtotal : 0.0, 2);

            if ($totalPedido > 0) {
                $datosDetalle = [
                    'moneda_id' => $cuenta->moneda_id,
                    'origen_type' => $pedido->getMorphClass(),
                    'origen_id' => $origenId,
                    'tipo_detalle' => CategoriaConsumo::RESTAURANTE->value,
                    'espacio_id' => $pedido->mesa_id,
                    'concepto' => "Comanda $pedido->codigo",
                    'descripcion' => count($itemsValues).' producto(s) solicitado(s)',
                    'cantidad' => 1,
                    'precio_unitario' => $totalPedido,
                    'subtotal' => $totalPedido,
                    'total' => $totalPedido,
                    'estado' => EstadoGeneral::Activo->value,
                    'creador_id' => $usuarioId,
                    'metadatos' => [
                        'pedido_codigo' => $pedido->codigo,
                        'pedido_id' => $pedido->id,
                        'items' => $itemsValues,
                    ],
                ];

                if ($detalleExistente instanceof CuentaDetalle) {
                    $detalles[] = $this->cuentas->actualizarDetalle($detalleExistente, $datosDetalle);
                } else {
                    $detalles[] = $this->cuentas->crearDetalle($cuenta, $datosDetalle);
                }
            }

            $this->cuentas->cargarPedidoEnCuenta($pedido, $cuenta->id);
        });

        $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);

        return $detalles;
    }
}
