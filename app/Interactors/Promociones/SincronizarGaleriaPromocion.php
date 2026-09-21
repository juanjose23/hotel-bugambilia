<?php

declare(strict_types=1);

namespace App\Interactors\Promociones;

use App\Repository\Models\Promociones\Promocion;
use App\Repository\Persistencia\Promociones\PromocionRepositorioInterface;

final readonly class SincronizarGaleriaPromocion
{
    public function __construct(
        private PromocionRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<int, string>  $imageUrls
     */
    public function ejecutar(Promocion $promocion, array $imageUrls): void
    {
        $this->repositorio->sincronizarImagenes($promocion, $imageUrls);
    }
}
