<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Repository\Models\Restaurante\RecetaTransformacionMateriaPrima;
use App\Repository\Persistencia\Restaurante\RecetaTransformacionMateriaPrimaRepositorioInterface;

final readonly class RegistrarReglaTransformacionMateriaPrima
{
    public function __construct(
        private RecetaTransformacionMateriaPrimaRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(array $datos): RecetaTransformacionMateriaPrima
    {
        return $this->repositorio->crear($datos);
    }
}
