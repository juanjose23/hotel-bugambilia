<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Operaciones;

use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class UnirMesasReserva
{
    public function __construct(
        private ReservaRepositorioInterface $reservas,
    ) {}

    /**
     * @param  array<int>  $mesasSecundariasIds
     */
    public function ejecutar(Reserva $reserva, int $mesaPrincipalId, array $mesasSecundariasIds): Reserva
    {
        return DB::transaction(function () use ($reserva, $mesaPrincipalId, $mesasSecundariasIds): Reserva {
            $principal = $this->reservas->detallePrincipalDe($reserva);
            $inicio = $principal->fecha_inicio;
            $fin = $principal->fecha_fin ?? $inicio;

            $idsExistentes = $reserva->detalles()
                ->with('reservable.espacio')
                ->get()
                ->map(fn ($detalle): ?int => $detalle->reservable?->espacio?->id)
                ->filter()
                ->map(fn (int $id): int => $id)
                ->all();

            foreach (array_values(array_unique($mesasSecundariasIds)) as $mesaId) {
                if ($mesaId === $mesaPrincipalId || in_array($mesaId, $idsExistentes, true)) {
                    continue;
                }

                $recurso = $this->reservas->resolverRecurso(TipoReserva::RESTAURANTE, $mesaId);

                $this->reservas->crearDetalle($reserva, $recurso, [
                    'parent_id' => $principal->id,
                    'estado' => EstadoReservaDetalle::CONFIRMADO,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'cantidad' => 1,
                    'adultos' => 0,
                    'ninos' => 0,
                    'precio_unitario' => 0,
                    'subtotal' => 0,
                    'notas' => 'Mesa unida a la reserva.',
                ]);
            }

            return $reserva->refresh()->load('detalles.reservable');
        });
    }
}
