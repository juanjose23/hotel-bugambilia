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
        $restaurantes = Espacio::query()->where('tipo', TipoEspacio::RESTAURANTE->value)->get();

        if ($restaurantes->isEmpty()) {
            return ['ambientes' => collect(), 'mesas' => collect()];
        }

        $restauranteIds = $restaurantes->pluck('id')->all();

        $ambientes = Espacio::query()
            ->whereIn('padre_id', $restauranteIds)
            ->where('tipo', '!=', TipoEspacio::MESA->value)
            ->orderBy('orden')
            ->get();

        $subEspacioIds = $ambientes->pluck('id')->all();
        $validParentIds = array_merge($restauranteIds, $subEspacioIds);

        $mesas = Espacio::query()
            ->with([
                'ubicacion.padre',
                'padre',
                'inventarioFijo.activo',
                'pedidosActivos' => fn ($query) => $query->whereIn('estado', [
                    EstadoPedido::ABIERTO,
                    EstadoPedido::EN_PREPARACION,
                    EstadoPedido::LISTO,
                    EstadoPedido::SERVIDO,
                ])->latest('id'),
            ])
            ->where('tipo', TipoEspacio::MESA->value)
            ->where(function ($query) use ($validParentIds): void {
                $query->whereNull('padre_id');
                if (! empty($validParentIds)) {
                    $query->orWhereIn('padre_id', $validParentIds);
                }
            })
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
        $reservasVigentes = $this->reservasVigentes->paraMesas($mesas->modelKeys());

        $mesas->each(function (Espacio $mesa) use ($reservasVigentes): void {
            $pedidos = $mesa->pedidosActivos;
            $mesa->setAttribute('cuentas_activas_count', $pedidos->count());
            $sum = $pedidos->sum('subtotal');
            $mesa->setAttribute('total_mesa', is_numeric($sum) ? (float) $sum : 0.0);

            $activoAsignado = $mesa->inventarioFijo->first()?->activo;
            $mesa->setAttribute('tiene_activo_asignado', $activoAsignado !== null);
            $mesa->setAttribute('activo_codigo', $activoAsignado?->codigo_inventario);
            $mesa->setAttribute('activo_nombre', $activoAsignado?->nombre_descriptivo);

            $primerPedido = $pedidos->first();
            $mesa->setAttribute('pedido_abierto_id', $primerPedido?->id);
            $mesa->setAttribute('pedido_abierto_codigo', $primerPedido?->codigo);
            $mesa->setAttribute('pedido_abierto_total', $primerPedido?->subtotal);

            $reserva = $reservasVigentes->get($mesa->id);
            if ($pedidos->isEmpty() && $reserva instanceof Reserva
                && in_array($mesa->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
                $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
                $preordenDatos = $reserva->ultimaEntradaBitacora('preorden');
                /** @var list<array<string, mixed>> $platosPreordenados */
                $platosPreordenados = is_array($preordenDatos['items'] ?? null) ? $preordenDatos['items'] : [];

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
