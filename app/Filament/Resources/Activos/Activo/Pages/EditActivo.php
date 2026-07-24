<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Pages;

use App\Filament\Resources\Activos\Activo\ActivoResource;
use App\Interactors\Activos\AsignarActivo;
use App\Models\Activos\Activo;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

/**
 * @extends EditRecord<Activo>
 */
class EditActivo extends EditRecord
{
    private AsignarActivo $asignarActivo;

    public function boot(AsignarActivo $asignarActivo): void
    {
        $this->asignarActivo = $asignarActivo;
    }

    protected static string $resource = ActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->getRecord()->loadMissing('asignacionActiva.asignable');
        $asignacion = $this->getRecord()->asignacionActiva;

        if ($asignacion && $asignacion->asignable) {
            $data['asignacion_tipo'] = $asignacion->asignable_type;
            $data['asignacion_destino_id'] = $asignacion->asignable_id;
            $data['asignacion_motivo'] = $asignacion->motivo;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['asignacion_tipo'], $data['asignacion_destino_id'], $data['asignacion_motivo']);

        return $data;
    }

    protected function afterSave(): void
    {
        $data = (array) $this->form->getRawState();

        $tipo = $data['asignacion_tipo'] ?? null;
        $destinoId = isset($data['asignacion_destino_id']) ? (int) $data['asignacion_destino_id'] : null;

        if ($tipo && $destinoId) {
            $this->getRecord()->loadMissing('asignacionActiva');
            $asignacionActual = $this->getRecord()->asignacionActiva;

            $cambio = ! $asignacionActual
                || $asignacionActual->asignable_type !== $tipo
                || $asignacionActual->asignable_id !== (int) $destinoId;

            if ($cambio) {
                try {
                    $this->asignarActivo->ejecutar(
                        $this->getRecord()->id,
                        $tipo,
                        (int) $destinoId,
                        (int) auth()->id(),
                        isset($data['asignacion_motivo']) && $data['asignacion_motivo'] !== '' ? (string) $data['asignacion_motivo'] : 'Actualización desde edición'
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }
}
