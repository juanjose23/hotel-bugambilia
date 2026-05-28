<?php

declare(strict_types=1);

namespace App\UseCases\Espacios\Mutations;

use App\Models\Espacios\Espacio;
use App\Models\Politicas\Politica;
use Illuminate\Support\Facades\DB;

/**
 * Caso de Uso: Asociar una política a un espacio físico.
 */
class AsignarPoliticaAEspacio
{
    /**
     * Ejecuta la asociación de la política al espacio.
     */
    public function execute(int $politicaId, int $espacioId): void
    {
        $espacio = Espacio::findOrFail($espacioId);
        $politica = Politica::findOrFail($politicaId);

        DB::transaction(function () use ($espacio, $politica) {
            // Comprobamos si ya está asociada
            if (! $espacio->politicas()->where('politica_id', $politica->id)->exists()) {
                $espacio->politicas()->attach($politica->id);
            }
        });
    }
}
