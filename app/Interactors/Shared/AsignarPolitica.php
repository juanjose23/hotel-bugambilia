<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AsignarPolitica
{
    public function execute(int $politicaId, Model $entity): void
    {
        Politica::query()->findOrFail($politicaId);

        if (! method_exists($entity, 'politicas')) {
            throw new \InvalidArgumentException('La entidad no soporta políticas.');
        }

        DB::transaction(function () use ($politicaId, $entity) {
            if (! $entity->politicas()->where('politica_id', $politicaId)->exists()) {
                $entity->politicas()->attach($politicaId);
            }
        });
    }
}
