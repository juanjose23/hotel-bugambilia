<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Catalogos;

use App\Repository\Models\Catalogos\Ubicacion;

interface UbicacionRepositorioInterface
{
    public function buscarMerma(): ?Ubicacion;

    /**
     * @param  array<int, string>|string  $tipos
     */
    public function buscarActivaPorTipo(array|string $tipos): ?Ubicacion;

    public function buscarPorId(int $id): ?Ubicacion;

    public function buscarPrimeraSubUbicacion(int $padreId, string $tipo): ?Ubicacion;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Ubicacion;

    public function obtenerMaxOrden(?int $padreId): int;
}
