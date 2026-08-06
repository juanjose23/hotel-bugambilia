<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoBaja\Pages;

use App\Enums\Activos\TipoBaja;
use App\Filament\Resources\Activos\ActivoBaja\ActivoBajaResource;
use App\Interactors\Activos\Gestion\DarDeBajaActivo;
use App\Repository\Models\Activos\ActivoBaja;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateActivoBaja extends CreateRecord
{
    protected DarDeBajaActivo $darDeBajaActivo;

    public function boot(DarDeBajaActivo $darDeBajaActivo): void
    {
        $this->darDeBajaActivo = $darDeBajaActivo;
    }

    protected static string $resource = ActivoBajaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $activoIdVal = $data['activo_id'] ?? 0;
        $activoId = is_numeric($activoIdVal) ? (int) $activoIdVal : 0;

        $motivoTipoVal = $data['motivo_tipo'] ?? '';
        $motivoTipo = $motivoTipoVal instanceof TipoBaja
            ? $motivoTipoVal
            : ((is_string($motivoTipoVal) || is_int($motivoTipoVal)) ? TipoBaja::from($motivoTipoVal) : TipoBaja::Robo);

        $motivoDetalleVal = $data['motivo_detalle'] ?? '';
        $motivoDetalle = is_string($motivoDetalleVal) ? $motivoDetalleVal : '';

        $valorResidualVal = $data['valor_residual'] ?? null;
        $valorResidual = is_numeric($valorResidualVal) ? (float) $valorResidualVal : null;

        $aprobadoPorIdVal = $data['aprobado_por_id'] ?? null;
        $aprobadoPorId = is_numeric($aprobadoPorIdVal) ? (int) $aprobadoPorIdVal : null;

        $docSoporteVal = $data['documento_soporte'] ?? null;
        $documentoSoporte = is_string($docSoporteVal) && $docSoporteVal !== '' ? $docSoporteVal : null;

        $this->darDeBajaActivo->execute(
            activoId: $activoId,
            motivoTipo: $motivoTipo,
            motivoDetalle: $motivoDetalle,
            userId: (int) auth()->id(),
            valorResidual: $valorResidual,
            aprobadoPorId: $aprobadoPorId,
            documentoSoporte: $documentoSoporte
        );

        // Devolver el registro de baja que se acaba de crear en la transacción del caso de uso
        return ActivoBaja::where('activo_id', $activoId)
            ->latest('id')
            ->firstOrFail();
    }
}
