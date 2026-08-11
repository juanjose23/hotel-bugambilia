<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Support\Collection;

final class PoliticaPresenter
{
    /**
     * @param  Collection<int, Politica>  $politicas
     * @return array<int, array<string, mixed>>
     */
    public function lista(Collection $politicas): array
    {
        return $politicas->map(fn (Politica $p): array => [
            'id' => $p->id,
            'nombre' => (string) ($p->nombre ?? ''),
            'descripcion' => $p->descripcion,
            'tipo' => 'Politica',
        ])->values()->all();
    }
}
