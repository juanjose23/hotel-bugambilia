<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Actions\Restaurante\NormalizarMetaDatosAction;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class UnirMesas
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly NormalizarMetaDatosAction $normalizarMetaDatosAction,
    ) {}

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
            $mesaPrincipal = $this->repositorio->obtenerEspacioPorId((int) $mesaPrincipalId);

            if (! $mesaPrincipal instanceof Espacio) {
                throw new DomainException("La mesa principal [{$mesaPrincipalId}] no existe.");
            }

            $reserva = $reservaId ? $this->repositorio->obtenerReservaPorId($reservaId) : null;

            $estadoObjetivo = $reservaId && $reserva?->estado !== EstadoReserva::CHECKED_IN
                ? EstadoEspacio::Reservado
                : EstadoEspacio::Ocupado;

            $metaPrincipal = $this->normalizarMetaDatosAction->ejecutar($mesaPrincipal->meta_datos);
            /** @var int[] $unidasExistentes */
            $unidasExistentes = isset($metaPrincipal['mesas_unidas']) && is_array($metaPrincipal['mesas_unidas'])
                ? $metaPrincipal['mesas_unidas']
                : [];

            $nuevasUnidas = array_values(array_unique(array_merge($unidasExistentes, $mesasSecundariasIds)));

            $metaPrincipal['mesas_unidas'] = $nuevasUnidas;
            $metaPrincipal['motivo_union'] = $motivo;
            if ($reservaId) {
                $metaPrincipal['reserva_id'] = $reservaId;
                $metaPrincipal['codigo_reserva'] = $reserva?->codigo_reserva;
            }

            $this->repositorio->actualizarEspacio($mesaPrincipal, [
                'estado' => $estadoObjetivo,
                'meta_datos' => $metaPrincipal,
            ]);

            foreach ($mesasSecundariasIds as $secundariaId) {
                $secundaria = $this->repositorio->obtenerEspacioPorId((int) $secundariaId);

                if (! $secundaria instanceof Espacio) {
                    continue;
                }

                $metaSecundaria = $this->normalizarMetaDatosAction->ejecutar($secundaria->meta_datos);
                $metaSecundaria['mesa_principal_id'] = $mesaPrincipalId;
                $metaSecundaria['mesa_principal_nombre'] = $mesaPrincipal->nombre;
                $metaSecundaria['motivo_union'] = $motivo;
                if ($reservaId) {
                    $metaSecundaria['reserva_id'] = $reservaId;
                    $metaSecundaria['codigo_reserva'] = $reserva?->codigo_reserva;
                }

                $this->repositorio->actualizarEspacio($secundaria, [
                    'estado' => $estadoObjetivo,
                    'meta_datos' => $metaSecundaria,
                ]);
            }
        });
    }
}
