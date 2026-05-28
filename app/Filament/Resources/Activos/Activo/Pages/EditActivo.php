<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Pages;

use App\Filament\Resources\Activos\Activo\ActivoResource;
use App\Models\Activos\Activo;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

/**
 * @extends EditRecord<Activo>
 */
class EditActivo extends EditRecord
{
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
        $data = $this->form->getRawState();

        $tipo = $data['asignacion_tipo'] ?? null;
        $destinoId = $data['asignacion_destino_id'] ?? null;

        if ($tipo && $destinoId) {
            $asignacionActual = $this->getRecord()->asignacionActiva;

            $cambio = ! $asignacionActual
                || $asignacionActual->asignable_type !== $tipo
                || $asignacionActual->asignable_id !== (int) $destinoId;

            if ($cambio) {
                try {
                    app(AsignarActivo::class)->execute(
                        $this->getRecord()->id,
                        $tipo,
                        (int) $destinoId,
                        auth()->id() ?? 1,
                        $data['asignacion_motivo'] ?? 'Actualización desde edición'
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }
}
