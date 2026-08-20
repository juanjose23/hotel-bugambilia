<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages;

use App\Actions\Limpieza\Ejecuciones\NormalizarChecklistEjecucionForm;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLimpiezaEjecucion extends CreateRecord
{
    protected NormalizarChecklistEjecucionForm $normalizarChecklist;

    protected static string $resource = LimpiezaEjecucionResource::class;

    public function boot(NormalizarChecklistEjecucionForm $normalizarChecklist): void
    {
        $this->normalizarChecklist = $normalizarChecklist;
    }

    public function getMaxContentWidth(): string
    {
        return '5xl';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['detalles_checklist'] = $this->normalizarChecklist->paraPersistencia(
            $data['detalles_checklist_items'] ?? []
        );

        unset($data['detalles_checklist_items']);

        return $data;
    }
}
