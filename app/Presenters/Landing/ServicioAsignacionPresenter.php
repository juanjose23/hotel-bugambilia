<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Repository\Models\Shared\ServicioAsignacion;
use Illuminate\Support\Collection;

final class ServicioAsignacionPresenter
{
    /**
     * @param  Collection<int, ServicioAsignacion>  $asignaciones
     * @return array<int, array<string, mixed>>
     */
    public function lista(Collection $asignaciones): array
    {
        return $asignaciones
            ->map(fn (ServicioAsignacion $sa): array => [
                'nombre' => (string) ($sa->servicio !== null ? $sa->servicio->nombre : ''),
                'descripcion' => (string) ($sa->servicio !== null ? ($sa->servicio->descripcion ?? '') : ''),
                'icono' => (string) ($sa->servicio !== null ? ($sa->servicio->icono ?? '') : ''),
                'incluido' => (bool) $sa->incluido,
            ])
            ->filter(fn (array $s): bool => $s['nombre'] !== '')
            ->values()
            ->all();
    }
}
