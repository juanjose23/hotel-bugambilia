<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Imagen;

class SincronizarGaleriaPlatoImagenes
{
    public function __construct(
        private readonly Imagen $imagen,
    ) {}

    /**
     * @param  array<int, string>  $imageUrls
     */
    public function execute(Plato $plato, array $imageUrls): void
    {
        $this->deleteRemovedImages($plato, $imageUrls);
        $this->saveOrderedImages($plato, $imageUrls);
    }

    /**
     * @param  array<int, string>  $currentUrls
     */
    private function deleteRemovedImages(Plato $plato, array $currentUrls): void
    {
        $existingUrls = $plato->imagenes()
            ->pluck('url')
            ->map(fn ($u) => is_scalar($u) ? (string) $u : '')
            ->values()
            ->all();

        $toDelete = array_diff(
            $existingUrls,
            $currentUrls,
        );

        if (! empty($toDelete)) {
            $this->imagen->query()
                ->where('imagenable_id', $plato->getKey())
                ->where('imagenable_type', $plato::class)
                ->whereIn('url', $toDelete)
                ->delete();
        }
    }

    /**
     * @param  array<int, string>  $imageUrls
     */
    private function saveOrderedImages(Plato $plato, array $imageUrls): void
    {
        foreach ($imageUrls as $index => $url) {
            $plato->imagenes()->updateOrCreate(
                ['url' => $url],
                ['orden' => $index + 1],
            );
        }
    }
}
