<?php

declare(strict_types=1);

namespace App\Interactors\Servicios;

use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;

class SincronizarGaleriaImagenes
{
    public function __construct(
        private readonly Imagen $imagen,
    ) {}

    /**
     * @param  array<int, string>  $imageUrls
     */
    public function execute(Servicio $servicio, array $imageUrls): void
    {
        $this->deleteRemovedImages($servicio, $imageUrls);
        $this->saveOrderedImages($servicio, $imageUrls);
    }

    /**
     * @param  array<int, string>  $currentUrls
     */
    private function deleteRemovedImages(Servicio $servicio, array $currentUrls): void
    {
        $existingUrls = $servicio->imagenes()
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
                ->where('imagenable_id', $servicio->getKey())
                ->where('imagenable_type', $servicio::class)
                ->whereIn('url', $toDelete)
                ->delete();
        }
    }

    /**
     * @param  array<int, string>  $imageUrls
     */
    private function saveOrderedImages(Servicio $servicio, array $imageUrls): void
    {
        foreach ($imageUrls as $index => $url) {
            $servicio->imagenes()->updateOrCreate(
                ['url' => $url],
                ['orden' => $index + 1],
            );
        }
    }
}
