<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Enums\Catalogos\TipoUbicacion;
use App\Repository\Models\Catalogos\Ubicacion;

final class ObtenerUbicacionesInventarioLavanderia
{
    /**
     * @return array<int>
     */
    public function execute(bool $incluirSucios = true): array
    {
        $tipos = [
            TipoUbicacion::LAVANDERIA->value,
            TipoUbicacion::BLANCOS_LIMPIOS->value,
        ];

        if ($incluirSucios) {
            $tipos[] = TipoUbicacion::BLANCOS_SUCIOS->value;
        }

        return Ubicacion::query()
            ->whereIn('tipo', $tipos)
            ->pluck('id')
            ->map(fn ($id): int => is_numeric($id) ? (int) $id : 0)
            ->filter(fn (int $id): bool => $id > 0)
            ->values()
            ->all();
    }
}
