<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Cuentas\CategoriaConsumo;
use App\Enums\Cuentas\TipoCuenta;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerCargoEnvioPedidoQuery;

final readonly class PrepararCuentaPedidoPublico
{
    public function __construct(
        private AbrirCuenta $abrirCuenta,
        private RegistrarDetalleCuenta $registrarDetalle,
        private CuentaRepositorioInterface $cuentas,
        private RestauranteRepositorioInterface $pedidos,
        private ObtenerCargoEnvioPedidoQuery $cargoEnvio,
    ) {}

    public function ejecutar(Pedido $pedido, float $costoEnvio): Cuenta
    {
        $cuenta = $pedido->cuenta;
        if (! $cuenta instanceof Cuenta) {
            $cuenta = $this->abrirCuenta->ejecutar(tipo: TipoCuenta::RESTAURANTE_DIRECTO, cliente: $pedido->cliente);
            $this->pedidos->actualizarPedido($pedido, ['cuenta_id' => $cuenta->id]);
            $pedido->setRelation('cuenta', $cuenta);
        }
        $pedido->loadMissing('items.plato');
        foreach ($pedido->items as $item) {
            if ($this->cuentas->detalleActivoConOrigen($cuenta, $item->getMorphClass(), $item->id) === null) {
                $this->registrarDetalle->ejecutar(cuenta: $cuenta,
                    concepto: $item->plato->nombre ?? 'Platillo', precioUnitario: (float) $item->precio_unitario,
                    cantidad: (float) $item->cantidad, origen: $item, tipoDetalle: CategoriaConsumo::RESTAURANTE->value);
            }
        }
        if ($costoEnvio > 0 && $this->cargoEnvio->ejecutar($pedido) === null) {
            $this->registrarDetalle->ejecutar(cuenta: $cuenta, concepto: "Envío de pedido {$pedido->codigo}",
                precioUnitario: $costoEnvio, tipoDetalle: CategoriaConsumo::TRANSPORTE->value,
                metadatos: ['pedido_delivery_id' => $pedido->id]);
        }

        return $cuenta->refresh();
    }
}
