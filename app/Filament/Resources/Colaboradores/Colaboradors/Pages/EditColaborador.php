<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Pages;

use App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource;
use App\Repository\Models\Personas\Persona;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

/**
 * @property Persona $record
 */
class EditColaborador extends EditRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected ?string $fotoUpload = null;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->fotoUpload = (isset($data['foto_upload']) && is_scalar($data['foto_upload'])) ? (string) $data['foto_upload'] : null;
        unset($data['foto_upload']);
        unset($data['foto_url']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->loadMissing('colaborador.imagen');
        $colaborador = $this->record->colaborador;

        if ($this->fotoUpload && $colaborador) {
            $imagenActual = $colaborador->imagen;

            if ($imagenActual && $imagenActual->url) {
                Storage::disk('public')->delete($imagenActual->url);
            }

            $colaborador->imagen()->updateOrCreate(
                [],
                ['url' => $this->fotoUpload]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('colaborador.imagen');
        $data['foto_url'] = $this->record->colaborador?->imagen?->url;

        return $data;
    }

    /** @return array<int, Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [];
    }
}
