<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoBaja\Pages;

use App\Enums\Activos\TipoBaja;
use App\Filament\Resources\Activos\ActivoBaja\ActivoBajaResource;
use App\Models\Activos\ActivoBaja;
use App\UseCases\Activos\Mutations\DarDeBajaActivo;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateActivoBaja extends CreateRecord
{
    protected static string $resource = ActivoBajaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        app(DarDeBajaActivo::class)->execute(
            activoId: (int) $data['activo_id'],
            motivoTipo: $data['motivo_tipo'] instanceof TipoBaja ? $data['motivo_tipo'] : TipoBaja::from($data['motivo_tipo']),
            motivoDetalle: $data['motivo_detalle'],
            userId: auth()->id() ?? 1,
            valorResidual: $data['valor_residual'] !== null ? (float) $data['valor_residual'] : null,
            aprobadoPorId: $data['aprobado_por_id'] !== null ? (int) $data['aprobado_por_id'] : null,
            documentoSoporte: $data['documento_soporte'] ?: null
        );

        // Devolver el registro de baja que se acaba de crear en la transacción del caso de uso
        return ActivoBaja::where('activo_id', $data['activo_id'])
            ->latest('id')
            ->firstOrFail();
    }
}
