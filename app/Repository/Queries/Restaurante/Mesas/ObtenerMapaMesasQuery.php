<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Collection;

final class ObtenerMapaMesasQuery
{
    /** @return array{ambientes: Collection<int, Espacio>, mesas: Collection<int, Espacio>} */
    public function ejecutar(): array
    {
        $restaurante = Espacio::query()->where('tipo', 'restaurante')->first();

        if (! $restaurante instanceof Espacio) {
            return ['ambientes' => collect(), 'mesas' => collect()];
        }

        $ambientes = Espacio::query()
            ->where('padre_id', $restaurante->id)
            ->whereIn('tipo', ['ambiente', 'terraza', 'bar'])
            ->orderBy('orden')
            ->get();

        $mesas = Espacio::query()
            ->with(['pedidosActivos' => fn ($query) => $query->whereIn('estado', [
                EstadoPedido::ABIERTO,
                EstadoPedido::EN_PREPARACION,
                EstadoPedido::SERVIDO,
            ])->latest('id')])
            ->where('padre_id', $restaurante->id)
            ->where('tipo', 'mesa')
            ->get()
            ->each(function (Espacio $mesa): void {
                $pedidos = $mesa->pedidosActivos;
                $mesa->cuentas_activas_count = $pedidos->count();
                $sum = $pedidos->sum('total');
                $mesa->total_mesa = is_numeric($sum) ? (float) $sum : 0.0;

                $primerPedido = $pedidos->first();
                $mesa->pedido_abierto_id = $primerPedido?->id;
                $mesa->pedido_abierto_codigo = $primerPedido?->codigo;
                $mesa->pedido_abierto_total = $primerPedido?->total;
            });

        return ['ambientes' => $ambientes, 'mesas' => $mesas];
    }
}
