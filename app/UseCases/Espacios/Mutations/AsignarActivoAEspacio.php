<?php

declare(strict_types=1);

namespace App\UseCases\Espacios\Mutations;

use App\Models\Espacios\Espacio;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;

/**
 * Caso de Uso: Asignar un activo fijo (accesorio/mobiliario) a un espacio.
 */
class AsignarActivoAEspacio
{
    private AsignarActivo $asignarActivo;

    public function __construct(AsignarActivo $asignarActivo)
    {
        $this->asignarActivo = $asignarActivo;
    }

    /**
     * Ejecuta la asignación del activo al espacio.
     */
    public function execute(int $activoId, int $espacioId, int $userId, ?string $motivo = null): void
    {
        Espacio::findOrFail($espacioId);

        $this->asignarActivo->execute(
            activoId: $activoId,
            asignableType: Espacio::class,
            asignableId: $espacioId,
            userId: $userId,
            motivo: $motivo ?: 'Asignación de activo fijo al espacio físico desde panel'
        );
    }
}
