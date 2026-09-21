<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Estrategias;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Persistencia\Catalogos\UbicacionRepositorioInterface;

final readonly class PutawayPolicy
{
    public function __construct(
        private UbicacionRepositorioInterface $ubicacionRepositorio,
    ) {}

    public function sugerirUbicacion(): Ubicacion
    {
        $ubicacion = $this->ubicacionRepositorio->buscarActivaPorTipo(['zona', 'almacen']);

        if (! $ubicacion) {
            throw new \RuntimeException(
                'No hay ubicaciones activas disponibles para asignar inventario. Crea al menos una ubicación de tipo "zona" o "almacen" en Catálogos > Ubicaciones.',
            );
        }

        return $ubicacion;
    }

    /**
     * Sugiere la primera sub-ubicación disponible (estante > nivel > posición)
     * dentro de la ubicación dada.
     */
    public function sugerirSubUbicacion(Ubicacion $ubicacion): ?Ubicacion
    {
        $estante = $this->ubicacionRepositorio->buscarPrimeraSubUbicacion($ubicacion->id, 'estante');

        if (! $estante) {
            return null;
        }

        $nivel = $this->ubicacionRepositorio->buscarPrimeraSubUbicacion($estante->id, 'nivel');

        if (! $nivel) {
            return $estante;
        }

        $posicion = $this->ubicacionRepositorio->buscarPrimeraSubUbicacion($nivel->id, 'posicion');

        return $posicion ?? $nivel;
    }
}
