<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Support\Facades\DB;

final class UnirMesas
{
    /**
     * Une una o varias mesas secundarias a una mesa principal para una reservación o para uso inmediato en servicio.
     *
     * @param  int[]  $mesasSecundariasIds
     */
    public function ejecutar(
        int $mesaPrincipalId,
        array $mesasSecundariasIds,
        ?int $reservaId = null,
        string $motivo = 'uso_inmediato'
    ): void {
        if (empty($mesasSecundariasIds)) {
            throw new DomainException('Debe seleccionar al menos una mesa secundaria para unir.');
        }

        if (in_array($mesaPrincipalId, $mesasSecundariasIds, true)) {
            throw new DomainException('La mesa principal no puede unirse a sí misma.');
        }

        DB::transaction(function () use ($mesaPrincipalId, $mesasSecundariasIds, $reservaId, $motivo): void {
            $mesaPrincipal = Espacio::query()->findOrFail($mesaPrincipalId);
            $reserva = $reservaId ? Reserva::query()->find($reservaId) : null;

            // Determinar estado según motivo
            $estadoObjetivo = $reservaId && $reserva?->estado !== EstadoReserva::CHECKED_IN
                ? EstadoEspacio::Reservado
                : EstadoEspacio::Ocupado;

            $metaPrincipal = $mesaPrincipal->meta_datos ?? [];
            $unidasExistentes = $metaPrincipal['mesas_unidas'] ?? [];
            $nuevasUnidas = array_unique(array_merge($unidasExistentes, $mesasSecundariasIds));

            $metaPrincipal['mesas_unidas'] = $nuevasUnidas;
            $metaPrincipal['motivo_union'] = $motivo;
            if ($reservaId) {
                $metaPrincipal['reserva_id'] = $reservaId;
                $metaPrincipal['codigo_reserva'] = $reserva?->codigo_reserva;
            }

            $mesaPrincipal->update([
                'estado' => $estadoObjetivo,
                'meta_datos' => $metaPrincipal,
            ]);

            foreach ($mesasSecundariasIds as $secundariaId) {
                $secundaria = Espacio::query()->findOrFail($secundariaId);
                $metaSecundaria = $secundaria->meta_datos ?? [];
                $metaSecundaria['mesa_principal_id'] = $mesaPrincipalId;
                $metaSecundaria['mesa_principal_nombre'] = $mesaPrincipal->nombre;
                $metaSecundaria['motivo_union'] = $motivo;
                if ($reservaId) {
                    $metaSecundaria['reserva_id'] = $reservaId;
                    $metaSecundaria['codigo_reserva'] = $reserva?->codigo_reserva;
                }

                $secundaria->update([
                    'estado' => $estadoObjetivo,
                    'meta_datos' => $metaSecundaria,
                ]);
            }
        });
    }
}
