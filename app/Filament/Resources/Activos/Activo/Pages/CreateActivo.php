<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Pages;

use App\Filament\Resources\Activos\Activo\ActivoResource;
use App\UseCases\Activos\Mutations\Gestion\RegistrarActivoFijo;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateActivo extends CreateRecord
{
    protected static string $resource = ActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $recepcionItemIdVal = $data['recepcion_item_id'] ?? null;
        $recepcionItemId = is_numeric($recepcionItemIdVal) ? (int) $recepcionItemIdVal : null;

        $productoIdVal = $data['producto_id'] ?? 0;
        $productoId = is_numeric($productoIdVal) ? (int) $productoIdVal : 0;

        $productoVarianteIdVal = $data['producto_variante_id'] ?? null;
        $productoVarianteId = is_numeric($productoVarianteIdVal) ? (int) $productoVarianteIdVal : null;

        $nombreDescriptivoVal = $data['nombre_descriptivo'] ?? '';
        $nombreDescriptivo = is_string($nombreDescriptivoVal) ? $nombreDescriptivoVal : '';

        $numeroSerieVal = $data['numero_serie'] ?? null;
        $numeroSerie = is_string($numeroSerieVal) && $numeroSerieVal !== '' ? $numeroSerieVal : null;

        $costoAdquisicionVal = $data['costo_adquisicion'] ?? null;
        $costoAdquisicion = is_numeric($costoAdquisicionVal) ? (float) $costoAdquisicionVal : null;

        $monedaIdVal = $data['moneda_id'] ?? null;
        $monedaId = is_numeric($monedaIdVal) ? (int) $monedaIdVal : null;

        $proveedorIdVal = $data['proveedor_id'] ?? null;
        $proveedorId = is_numeric($proveedorIdVal) ? (int) $proveedorIdVal : null;

        $fechaAdquisicionVal = $data['fecha_adquisicion'] ?? now()->toDateString();
        $fechaAdquisicion = is_string($fechaAdquisicionVal) ? $fechaAdquisicionVal : now()->toDateString();

        $asignacionTypeVal = $data['asignacion_tipo'] ?? null;
        $asignacionType = is_string($asignacionTypeVal) && $asignacionTypeVal !== '' ? $asignacionTypeVal : null;

        $asignableIdVal = $data['asignacion_destino_id'] ?? null;
        $asignableId = is_numeric($asignableIdVal) ? (int) $asignableIdVal : null;

        $asignacionMotivoVal = $data['asignacion_motivo'] ?? null;
        $asignacionMotivo = is_string($asignacionMotivoVal) && $asignacionMotivoVal !== '' ? $asignacionMotivoVal : null;

        return app(RegistrarActivoFijo::class)->execute(
            recepcionItemId: $recepcionItemId,
            productoId: $productoId,
            productoVarianteId: $productoVarianteId,
            nombreDescriptivo: $nombreDescriptivo,
            numeroSerie: $numeroSerie,
            costoAdquisicion: $costoAdquisicion,
            monedaId: $monedaId,
            proveedorId: $proveedorId,
            fechaAdquisicion: $fechaAdquisicion,
            userId: (int) auth()->id(),
            asignacionType: $asignacionType,
            asignableId: $asignableId,
            asignacionMotivo: $asignacionMotivo
        );
    }
}
