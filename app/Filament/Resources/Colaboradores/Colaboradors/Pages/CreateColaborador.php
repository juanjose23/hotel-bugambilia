<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Pages;

use App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource;
use App\Repository\Models\Personas\Persona;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;

/**
 * @property Persona $record
 */
class CreateColaborador extends CreateRecord
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

        return $data;
    }

    protected function afterSave(): void
    {
        $colaborador = $this->record->colaborador;

        if ($this->fotoUpload && $colaborador) {
            $colaborador->imagen()->create([
                'url' => $this->fotoUpload,
            ]);
        }
    }

    /** @return array<int, Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [];
    }
}
