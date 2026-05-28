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
        return app(RegistrarActivoFijo::class)->execute(
            recepcionItemId: isset($data['recepcion_item_id']) && $data['recepcion_item_id'] !== '' ? (int) $data['recepcion_item_id'] : null,
            productoId: (int) $data['producto_id'],
            productoVarianteId: isset($data['producto_variante_id']) && $data['producto_variante_id'] !== '' ? (int) $data['producto_variante_id'] : null,
            nombreDescriptivo: $data['nombre_descriptivo'],
            numeroSerie: $data['numero_serie'] ?: null,
            costoAdquisicion: $data['costo_adquisicion'] !== null ? (float) $data['costo_adquisicion'] : null,
            monedaId: $data['moneda_id'] !== null ? (int) $data['moneda_id'] : null,
            proveedorId: $data['proveedor_id'] !== null ? (int) $data['proveedor_id'] : null,
            fechaAdquisicion: $data['fecha_adquisicion'] ?: now()->toDateString(),
            userId: auth()->id() ?? 1,
            asignacionType: $data['asignacion_tipo'] ?: null,
            asignableId: $data['asignacion_destino_id'] !== null ? (int) $data['asignacion_destino_id'] : null,
            asignacionMotivo: $data['asignacion_motivo'] ?: null
        );
    }
}
