<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Collection;

final class ObtenerMapaMesasQuery
{
    public function __construct(
        private readonly ObtenerReservasVigentesMesaQuery $reservasVigentes,
    ) {}

    /** @return array{ambientes: Collection<int, Espacio>, mesas: Collection<int, Espacio>} */
    public function ejecutar(): array
    {
        $restaurante = Espacio::query()->where('tipo', TipoEspacio::RESTAURANTE->value)->first();

        if (! $restaurante instanceof Espacio) {
            return ['ambientes' => collect(), 'mesas' => collect()];
        }

        $ambientes = Espacio::query()
            ->where('padre_id', $restaurante->id)
            ->whereIn('tipo', [
                TipoEspacio::AMBIENTE->value,
                TipoEspacio::TERRAZA->value,
                TipoEspacio::BAR->value,
            ])
            ->orderBy('orden')
            ->get();

        $mesas = Espacio::query()
            ->with(['pedidosActivos' => fn ($query) => $query->whereIn('estado', [
                EstadoPedido::ABIERTO,
                EstadoPedido::EN_PREPARACION,
                EstadoPedido::LISTO,
                EstadoPedido::SERVIDO,
            ])->latest('id')])
            ->where('padre_id', $restaurante->id)
            ->where('tipo', TipoEspacio::MESA->value)
            ->get();
        $reservasVigentes = $this->reservasVigentes->paraMesas($mesas->modelKeys());

        $mesas->each(function (Espacio $mesa) use ($reservasVigentes): void {
            $pedidos = $mesa->pedidosActivos;
            $mesa->setAttribute('cuentas_activas_count', $pedidos->count());
            $sum = $pedidos->sum('subtotal');
            $mesa->setAttribute('total_mesa', is_numeric($sum) ? (float) $sum : 0.0);

            $primerPedido = $pedidos->first();
            $mesa->setAttribute('pedido_abierto_id', $primerPedido?->id);
            $mesa->setAttribute('pedido_abierto_codigo', $primerPedido?->codigo);
            $mesa->setAttribute('pedido_abierto_total', $primerPedido?->subtotal);

            $reserva = $reservasVigentes->get($mesa->id);
            if ($pedidos->isEmpty() && $reserva instanceof Reserva
                && in_array($mesa->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
                $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
                $reservaMeta = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
                $platosPreordenados = is_array($reservaMeta['platos_preordenados'] ?? null)
                    ? $reservaMeta['platos_preordenados']
                    : (is_array($reservaMeta['items_preorden'] ?? null) ? $reservaMeta['items_preorden'] : []);

                $mesa->setAttribute('estado', EstadoEspacio::Reservado);
                $mesa->setAttribute('meta_datos', [
                    ...$meta,
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'nombre_cliente' => $reserva->nombre_cliente,
                    'hora_reserva' => $reserva->hora_reserva,
                    'total_personas' => $reserva->adultos,
                    'platos_preordenados' => $platosPreordenados,
                    'platos_preordenados_count' => count($platosPreordenados),
                ]);
            } elseif ($pedidos->isEmpty() && $mesa->estado === EstadoEspacio::Reservado) {
                $mesa->setAttribute('estado', EstadoEspacio::Disponible);
            }
        });

        return ['ambientes' => $ambientes, 'mesas' => $mesas];
    }
}
