<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Servicios;

use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\ServicioAsignacion;

interface ServicioRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Servicio;

    public function buscarActivoConPrecios(int $id): ?Servicio;

    public function buscarPorId(int $id): ?Servicio;

    /** @param array<int, string> $imageUrls */
    public function sincronizarImagenes(Servicio $servicio, array $imageUrls): void;

    public function asignarAServiceable(
        int $servicioId,
        string $serviceableType,
        int $serviceableId,
        bool $incluido = false,
        int $estado = 1
    ): ServicioAsignacion;
}
