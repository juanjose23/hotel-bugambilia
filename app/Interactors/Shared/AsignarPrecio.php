<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\BusinessLogic\Shared\ServicioPrecios;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AsignarPrecio
{
    public function __construct(
        private readonly ServicioPrecios $servicioPrecios,
    ) {}

    public function execute(
        string $priceableType,
        int $priceableId,
        int $monedaId,
        float $precio,
        string $fechaInicio,
        ?string $fechaFin = null,
        int $estado = 1,
        bool $esOferta = false,
        string $tipoPrecio = 'base',
    ): Precio {
        $this->loadPriceable($priceableType, $priceableId);
        $this->assertPrecioNoNegativo($precio);

        return DB::transaction(function () use (
            $priceableType, $priceableId, $monedaId, $precio,
            $fechaInicio, $fechaFin, $estado, $esOferta, $tipoPrecio,
        ) {
            $this->servicioPrecios->expirarPreciosAnterioresSiCorresponde(
                priceableType: $priceableType,
                priceableId: $priceableId,
                monedaId: $monedaId,
                tipoPrecio: $tipoPrecio,
                estado: $estado,
                esOferta: $esOferta,
            );

            return $this->servicioPrecios->crearPrecio(
                priceableType: $priceableType,
                priceableId: $priceableId,
                monedaId: $monedaId,
                precio: $precio,
                fechaInicio: $fechaInicio,
                fechaFin: $fechaFin,
                estado: $estado,
                esOferta: $esOferta,
                tipoPrecio: $tipoPrecio,
            );
        });
    }

    private function loadPriceable(string $type, int $id): void
    {
        /** @var Model $model */
        $model = new $type;
        $model->query()->findOrFail($id);
    }

    private function assertPrecioNoNegativo(float $precio): void
    {
        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }
    }
}
