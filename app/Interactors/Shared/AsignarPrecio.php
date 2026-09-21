<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\Repository\Models\Shared\Precio;
use App\Repository\Persistencia\Shared\PrecioRepositorioInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final readonly class AsignarPrecio
{
    public function __construct(
        private PrecioRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(
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
            $this->repositorio->expirarPreciosAnterioresSiCorresponde(
                priceableType: $priceableType,
                priceableId: $priceableId,
                monedaId: $monedaId,
                tipoPrecio: $tipoPrecio,
                estado: $estado,
                esOferta: $esOferta,
            );

            return $this->repositorio->crear([
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
        });
    }

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
        return $this->ejecutar(
            $priceableType,
            $priceableId,
            $monedaId,
            $precio,
            $fechaInicio,
            $fechaFin,
            $estado,
            $esOferta,
            $tipoPrecio,
        );
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
