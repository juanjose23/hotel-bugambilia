<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\BusinessLogic\Restaurante\Mesas\ValidarTransicionMesa;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class ProcesarNoShowsRestaurante
{
    public function __construct(
        private ReservaRepositorioInterface $reservas,
        private RestauranteRepositorioInterface $restaurante,
        private ValidarTransicionMesa $validarTransicion,
    ) {}

    /**
     * Procesa reservaciones de restaurante en estado CONFIRMADA o PENDIENTE que hayan excedido el tiempo de tolerancia (30 minutos)
     * las marca como CANCELADAS por No-Show y libera la mesa asignada.
     *
     * @param  int  $minutosTolerancia  Minutos de gracia antes de considerar No-Show (predeterminado: 30)
     * @return int Cantidad de reservaciones procesadas como No-Show
     */
    public function ejecutar(int $minutosTolerancia = 30): int
    {
        $ahora = Carbon::now();
        $fechaHoy = $ahora->toDateString();
        $horaLimiteStr = $ahora->subMinutes($minutosTolerancia)->format('H:i');

        $reservacionesVencidas = $this->reservas->obtenerReservasRestauranteVencidas($fechaHoy, $horaLimiteStr);

        $procesados = 0;

        foreach ($reservacionesVencidas as $reserva) {
            DB::transaction(function () use ($reserva, &$procesados): void {
                $this->reservas->cambiarEstado(
                    reserva: $reserva,
                    estado: EstadoReserva::CANCELADA,
                    motivo: 'No-Show / Tiempo de tolerancia expirado (30 min)',
                );

                if ($reserva->espacio_id !== null) {
                    $mesa = $this->restaurante->obtenerEspacioPorId($reserva->espacio_id);
                    if ($mesa instanceof Espacio && $mesa->estado === EstadoEspacio::Reservado) {
                        $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
                        unset(
                            $meta['reserva_id'],
                            $meta['codigo_reserva'],
                            $meta['platos_preordenados'],
                            $meta['nombre_cliente']
                        );

                        $this->validarTransicion->validar($mesa->estado, EstadoEspacio::Disponible, MotivoTransicionMesa::CancelacionReserva);
                        $this->restaurante->actualizarEspacio($mesa, [
                            'estado' => EstadoEspacio::Disponible,
                            'meta_datos' => $meta,
                        ]);
                    }
                }

                $procesados++;
            });
        }

        return $procesados;
    }
}
