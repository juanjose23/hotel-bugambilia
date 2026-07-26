<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;

final class ValidarCapacidadMesasRestaurante
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Valida que no se agreguen más mesas de las configuradas en el Espacio Restaurante.
     *
     * @param  array<string, mixed>  $metaRestaurante
     *
     * @throws DomainException
     */
    public function validar(int $restauranteId, array $metaRestaurante, ?int $espacioIgnoradoId = null): void
    {
        $capacidadMaxima = isset($metaRestaurante['capacidad_mesas']) && is_numeric($metaRestaurante['capacidad_mesas'])
            ? (int) $metaRestaurante['capacidad_mesas']
            : null;

        if ($capacidadMaxima === null || $capacidadMaxima <= 0) {
            return;
        }

        $mesasActuales = $this->repositorio->contarMesasEnRestaurante($restauranteId, $espacioIgnoradoId);

        if ($mesasActuales >= $capacidadMaxima) {
            throw new DomainException(
                "Se ha alcanzado la capacidad máxima de mesas ({$capacidadMaxima}) configurada para el restaurante."
            );
        }
    }
}
