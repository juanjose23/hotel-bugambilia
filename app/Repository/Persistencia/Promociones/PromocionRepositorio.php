<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Promociones;

use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionBeneficio;

final class PromocionRepositorio implements PromocionRepositorioInterface
{
    public function buscarBeneficioPorId(int $id): ?PromocionBeneficio
    {
        return PromocionBeneficio::query()->find($id);
    }

    /**
     * @param  array<int, string>  $imageUrls
     */
    public function sincronizarImagenes(Promocion $promocion, array $imageUrls): void
    {
        /** @var array<int, string> $existingUrls */
        $existingUrls = $promocion->imagenes()
            ->pluck('url')
            ->map(fn (mixed $u): string => is_scalar($u) ? strval($u) : '')
            ->toArray();

        $toDelete = array_diff($existingUrls, $imageUrls);
        if ($toDelete !== []) {
            $promocion->imagenes()->whereIn('url', $toDelete)->delete();
        }

        foreach ($imageUrls as $index => $url) {
            if ($url === '') {
                continue;
            }

            $promocion->imagenes()->updateOrCreate(
                ['url' => $url],
                ['orden' => $index + 1],
            );
        }
    }
}
