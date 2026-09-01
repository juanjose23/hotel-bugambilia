<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Support\Collection;

final class PoliticaPresenter
{
    /**
     * @param  Collection<int, Politica>  $politicas
     * @return array<int, array{id: int, titulo: string, descripcion: string, aplica_penalizacion: bool, penalizaciones: array<int, array{min_unidades: ?int, max_unidades: ?int, unidad: string, porcentaje: float, aplica_no_show: bool}>}>
     */
    public function lista(Collection $politicas): array
    {
        return $politicas->map(fn (Politica $p): array => [
            'id' => (int) $p->id,
            'titulo' => (string) ($p->titulo ?? $p->nombre ?? 'Política de Reserva'),
            'descripcion' => (string) ($p->descripcion ?? ''),
            'aplica_penalizacion' => (bool) $p->aplica_penalizacion,
            'penalizaciones' => $p->penalizaciones->map(fn ($pen): array => [
                'min_unidades' => $pen->min_unidades !== null ? (int) $pen->min_unidades : null,
                'max_unidades' => $pen->max_unidades !== null ? (int) $pen->max_unidades : null,
                'unidad' => (string) $pen->unidad->value,
                'porcentaje' => (float) $pen->porcentaje,
                'aplica_no_show' => (bool) $pen->aplica_no_show,
            ])->values()->all(),
        ])->values()->all();
    }
}
