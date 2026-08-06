<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\BusinessLogic\Restaurante\Mesas\ValidarTransicionMesa;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Interactors\Limpieza\Ejecucion\RegistrarSolicitudLimpieza;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMesaQuery;
use DomainException;
use InvalidArgumentException;

final class CambiarEstadoMesa
{
    public function __construct(
        private readonly ObtenerMesaQuery $mesas,
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly ValidarTransicionMesa $validarTransicion,
        private readonly RegistrarSolicitudLimpieza $registrarSolicitudLimpieza,
    ) {}

    public function ejecutar(
        int $mesaId,
        EstadoEspacio $estado,
        MotivoTransicionMesa $motivo = MotivoTransicionMesa::Manual,
    ): Espacio {
        $mesa = $this->mesas->porId($mesaId);

        if (! $mesa instanceof Espacio) {
            throw new InvalidArgumentException('La mesa seleccionada no existe.');
        }

        if ($mesa->estado !== $estado) {
            $tieneSolicitudActiva = SolicitudLimpieza::query()
                ->where('limpiable_type', $mesa->getMorphClass())
                ->where('limpiable_id', $mesa->id)
                ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
                ->exists();

            if ($tieneSolicitudActiva && ! in_array($estado, [EstadoEspacio::Sucio, EstadoEspacio::Limpieza], true)) {
                throw new DomainException(
                    "No se puede cambiar el estado de la mesa '{$mesa->nombre}' porque tiene una solicitud de limpieza activa."
                );
            }
        }

        $this->validarTransicion->validar($mesa->estado, $estado, $motivo);

        $mesa->estado = $estado;
        $this->repositorio->guardarMesa($mesa);

        if (in_array($estado, [EstadoEspacio::Sucio, EstadoEspacio::Limpieza], true)) {
            $this->registrarSolicitudLimpieza->execute(
                limpiable: $mesa,
                prioridad: 'normal',
                notas: "Solicitud de limpieza generada automáticamente por cambio de estado de la mesa '{$mesa->nombre}'"
            );
        }

        return $mesa;
    }
}
