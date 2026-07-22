<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared;

use App\Repository\Models\Shared\Precio;

class ServicioPrecios
{
    public function __construct(
        private readonly Precio $precioModel,
    ) {}

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

        $this->precioModel->query()
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

    public function crearPrecio(
        string $priceableType,
        int $priceableId,
        int $monedaId,
        float $precio,
        string $fechaInicio,
        ?string $fechaFin,
        int $estado,
        bool $esOferta,
        string $tipoPrecio,
    ): Precio {
        return $this->precioModel->query()->create([
            'priceable_type' => $priceableType,
            'priceable_id' => $priceableId,
            'moneda_id' => $monedaId,
            'precio' => $precio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'estado' => $estado,
            'es_oferta' => $esOferta,
            'tipo_precio' => $tipoPrecio,
        ]);
    }
}
