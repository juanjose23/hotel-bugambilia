<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Clientes\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BuscarClientesRapidoQuery
{
    /**
     * Busca clientes por nombre, teléfono o identificación de su persona asociada.
     *
     * @return Collection<int, Cliente>
     */
    public function ejecutar(string $busqueda): Collection
    {
        $termino = trim($busqueda);

        if ($termino === '') {
            return collect();
        }

        return Cliente::query()
            ->whereHas('persona', function (Builder $persona) use ($termino): void {
                $persona->where('primer_nombre', 'LIKE', "%{$termino}%")
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
            ->with(['persona.personaNatural', 'persona.personaJuridica'])
            ->limit(15)
            ->get();
    }
}
