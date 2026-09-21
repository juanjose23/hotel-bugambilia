<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Repository\Models\Restaurante\RecetaTransformacionMateriaPrima;

final class RecetaTransformacionMateriaPrimaRepositorio implements RecetaTransformacionMateriaPrimaRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): RecetaTransformacionMateriaPrima
    {
        return RecetaTransformacionMateriaPrima::query()->create($datos);
    }
}
