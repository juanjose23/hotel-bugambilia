<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Catalogos;

use App\Repository\Models\Catalogos\Ubicacion;

final class UbicacionRepositorio implements UbicacionRepositorioInterface
{
    public function buscarMerma(): ?Ubicacion
    {
        return Ubicacion::query()
            ->where('tipo', 'zona')
            ->where(function ($q) {
                $q->where('nombre', 'like', '%merma%')
                    ->orWhere('descripcion', 'like', '%merma%');
            })
            ->first();
    }

    /**
     * @param  array<int, string>|string  $tipos
     */
    public function buscarActivaPorTipo(array|string $tipos): ?Ubicacion
    {
        $tiposArray = is_array($tipos) ? $tipos : [$tipos];

        return Ubicacion::query()
            ->whereIn('tipo', $tiposArray)
            ->where('estado', 1)
            ->first();
    }

    public function buscarPorId(int $id): ?Ubicacion
    {
        return Ubicacion::query()->where('estado', 1)->find($id);
    }

    public function buscarPrimeraSubUbicacion(int $padreId, string $tipo): ?Ubicacion
    {
        return Ubicacion::query()
            ->where('tipo', $tipo)
            ->where('padre_id', $padreId)
            ->where('estado', 1)
            ->first();
    }

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Ubicacion
    {
        return Ubicacion::create($datos);
    }

    public function obtenerMaxOrden(?int $padreId): int
    {
        $maxVal = Ubicacion::query()->where('padre_id', $padreId)->max('orden');

        return is_numeric($maxVal) ? (int) $maxVal : 0;
    }
}
