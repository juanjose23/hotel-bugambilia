<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Shared;

use App\Repository\Models\Shared\Precio;

final class PrecioRepositorio implements PrecioRepositorioInterface
{
    public function expirarPreciosAnterioresSiCorresponde(
        string $priceableType,
        int $priceableId,
        int $monedaId,
        string $tipoPrecio,
        int $estado,
        bool $esOferta,
    ): void {
        $debeExpirarAnteriores = $estado === 1 && ! $esOferta;

        if (! $debeExpirarAnteriores) {
            return;
        }

        Precio::query()
            ->where('priceable_type', $priceableType)
            ->where('priceable_id', $priceableId)
            ->where('moneda_id', $monedaId)
            ->where('tipo_precio', $tipoPrecio)
            ->where('estado', 1)
            ->where('es_oferta', false)
            ->update([
                'estado' => 2,
                'fecha_fin' => now()->subDay()->toDateString(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Precio
    {
        return Precio::query()->create($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Precio $precio, array $datos): Precio
    {
        $precio->update($datos);

        return $precio;
    }
}
