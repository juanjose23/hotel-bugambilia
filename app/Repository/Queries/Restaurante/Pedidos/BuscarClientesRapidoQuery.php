<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Personas\Persona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BuscarClientesRapidoQuery
{
    /**
     * Busca personas con cliente asociado por nombre, teléfono o identificación.
     *
     * @return Collection<int, Persona>
     */
    public function ejecutar(string $busqueda): Collection
    {
        $termino = trim($busqueda);

        if ($termino === '') {
            return collect();
        }

        return Persona::query()
            ->whereHas('cliente')
            ->where(function (Builder $q) use ($termino): void {
                $q->where('primer_nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('segundo_nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono', 'LIKE', "%{$termino}%")
                    ->orWhereHas('personaNatural', function (Builder $pn) use ($termino): void {
                        $pn->where('primer_apellido', 'LIKE', "%{$termino}%")
                            ->orWhere('segundo_apellido', 'LIKE', "%{$termino}%")
                            ->orWhere('numero_identificacion', 'LIKE', "%{$termino}%");
                    })
                    ->orWhereHas('personaJuridica', function (Builder $pj) use ($termino): void {
                        $pj->where('razon_social', 'LIKE', "%{$termino}%")
                            ->orWhere('numero_identificacion', 'LIKE', "%{$termino}%");
                    });
            })
            ->with(['personaNatural', 'personaJuridica', 'cliente'])
            ->limit(15)
            ->get();
    }
}
