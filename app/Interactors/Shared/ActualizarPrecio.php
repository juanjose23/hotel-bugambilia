<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\Repository\Models\Shared\Precio;
use App\Repository\Persistencia\Shared\PrecioRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class ActualizarPrecio
{
    public function __construct(
        private PrecioRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function ejecutar(Precio $record, array $data, bool $hasTipoPrecioField = false): Precio
    {
        return DB::transaction(function () use ($record, $data, $hasTipoPrecioField): Precio {
            $estadoRaw = $data['estado'] ?? 1;
            $estado = is_numeric($estadoRaw) ? (int) $estadoRaw : 1;

            $esOferta = (bool) ($data['es_oferta'] ?? false);

            $monedaIdRaw = $data['moneda_id'] ?? 0;
            $monedaId = is_numeric($monedaIdRaw) ? (int) $monedaIdRaw : 0;

            $tipoPrecioRaw = $data['tipo_precio'] ?? 'base';
            $tipoPrecio = $hasTipoPrecioField && is_scalar($tipoPrecioRaw) ? (string) $tipoPrecioRaw : 'base';

            $priceableType = $record->getAttribute('priceable_type');
            $priceableId = $record->getAttribute('priceable_id');

            $this->repositorio->expirarPreciosAnterioresSiCorresponde(
                priceableType: is_string($priceableType) ? $priceableType : '',
                priceableId: is_scalar($priceableId) ? intval($priceableId) : 0,
                monedaId: $monedaId,
                tipoPrecio: $tipoPrecio,
                estado: $estado,
                esOferta: $esOferta,
            );

            $precioRaw = $data['precio'] ?? 0;
            $precio = is_numeric($precioRaw) ? (float) $precioRaw : 0.0;

            $fechaInicioRaw = $data['fecha_inicio'] ?? '';
            $fechaInicio = is_scalar($fechaInicioRaw) ? (string) $fechaInicioRaw : '';

            $fechaFinRaw = $data['fecha_fin'] ?? null;
            $fechaFin = is_scalar($fechaFinRaw) && (string) $fechaFinRaw !== '' ? (string) $fechaFinRaw : null;

            return $this->repositorio->actualizar($record, [
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
}
