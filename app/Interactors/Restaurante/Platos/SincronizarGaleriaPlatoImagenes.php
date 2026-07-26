<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Platos;

use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final class SincronizarGaleriaPlatoImagenes
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<int, string>  $imageUrls
     */
    public function ejecutar(Plato $plato, array $imageUrls): void
    {
        $this->deleteRemovedImages($plato, $imageUrls);
        $this->saveOrderedImages($plato, $imageUrls);
    }

    /**
     * @param  array<int, string>  $currentUrls
     */
    private function deleteRemovedImages(Plato $plato, array $currentUrls): void
    {
        $existingUrls = $this->repositorio
            ->obtenerUrlsImagenesDeModelo($plato::class, $plato->id)
            ->all();

        $toDelete = array_diff($existingUrls, $currentUrls);

        if (! empty($toDelete)) {
            $this->repositorio->eliminarImagenesPorUrls($plato::class, $plato->id, $toDelete);
        }
    }

    /**
     * @param  array<int, string>  $imageUrls
     */
    private function saveOrderedImages(Plato $plato, array $imageUrls): void
    {
        foreach ($imageUrls as $index => $url) {
            $this->repositorio->sincronizarImagenOrden(
                $plato::class,
                $plato->id,
                $url,
                $index + 1
            );
        }
    }
}
