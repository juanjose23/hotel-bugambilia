<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Promociones;

use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionBeneficio;

interface PromocionRepositorioInterface
{
    /**
     * @param  array<int, string>  $imageUrls
     */
    public function sincronizarImagenes(Promocion $promocion, array $imageUrls): void;

    public function buscarBeneficioPorId(int $id): ?PromocionBeneficio;
}
