<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosServidosCuentaQuery;

final readonly class FinalizarPedidosServidosCuenta
{
    public function __construct(private ObtenerPedidosServidosCuentaQuery $pedidos, private CerrarPedidoMesa $cerrar) {}

    /** @return list<string> */
    public function ejecutar(Cuenta $cuenta, ?int $usuarioId): array
    {
        $mesas = [];
        foreach ($this->pedidos->ejecutar($cuenta->id) as $pedido) {
            $this->cerrar->ejecutar($pedido, usuarioId: $usuarioId);
            $mesa = $pedido->mesa;
            if ($mesa !== null && $mesa->refresh()->estado === EstadoEspacio::Sucio) {
                $mesas[] = $mesa->nombre;
            }
        }

        return array_values(array_unique($mesas));
    }
}
