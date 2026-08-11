<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\Turno;
use Illuminate\Database\Eloquent\Model;

final readonly class ResolverTurnoParaLimpiable
{
    public function execute(Model $limpiable): ?Turno
    {
        $ubicacion = $this->ubicacionDelLimpiable($limpiable);

        $currentUbicacion = $ubicacion;
        while ($currentUbicacion) {
            $turno = Turno::query()
                ->where('estado', true)
                ->whereHas('carritos', fn ($q) => $q->where('padre_id', $currentUbicacion->id))
                ->first();

            if ($turno !== null) {
                return $turno;
            }

            $currentUbicacion->loadMissing('padre');
            $currentUbicacion = $currentUbicacion->padre;
        }

        return Turno::query()->where('estado', true)->first() ?: Turno::query()->first();
    }

    private function ubicacionDelLimpiable(Model $limpiable): ?Ubicacion
    {
        if (! method_exists($limpiable, 'ubicacion')) {
            return null;
        }

        $limpiable->loadMissing('ubicacion');

        /** @phpstan-ignore property.notFound */
        $ubicacion = $limpiable->ubicacion;

        return $ubicacion instanceof Ubicacion ? $ubicacion : null;
    }
}
