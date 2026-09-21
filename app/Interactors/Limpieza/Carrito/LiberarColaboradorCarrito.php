<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use Carbon\Carbon;

final readonly class LiberarColaboradorCarrito
{
    public function __construct(
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    /**
     * Libera al colaborador de su carro asignado para una fecha (por defecto hoy).
     */
    public function execute(int $colaboradorId, ?string $fecha = null): bool
    {
        return $this->ejecutar($colaboradorId, $fecha);
    }

    public function ejecutar(int $colaboradorId, ?string $fecha = null): bool
    {
        $fecha = $fecha ?: Carbon::now()->toDateString();

        return $this->limpiezaRepositorio->liberarCarritoDeColaborador($colaboradorId, $fecha);
    }
}
