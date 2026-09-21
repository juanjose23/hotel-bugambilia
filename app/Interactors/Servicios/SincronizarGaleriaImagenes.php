<?php

declare(strict_types=1);

namespace App\Interactors\Servicios;

use App\Repository\Models\Servicios\Servicio;
use App\Repository\Persistencia\Servicios\ServicioRepositorioInterface;

final readonly class SincronizarGaleriaImagenes
{
    public function __construct(
        private ServicioRepositorioInterface $servicioRepositorio,
    ) {}

    /**
     * @param  array<int, string>  $imageUrls
     */
    public function execute(Servicio $servicio, array $imageUrls): void
    {
        $this->servicioRepositorio->sincronizarImagenes($servicio, $imageUrls);
    }
}
