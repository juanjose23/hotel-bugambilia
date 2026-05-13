<?php

namespace App\Filament\Resources\Compras\Proveedors\Pages;

use App\Filament\Resources\Compras\Proveedors\ProveedorResource;
use App\Models\Compras\Proveedor;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProveedor extends EditRecord
{
    protected static string $resource = ProveedorResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Proveedor $record */
        $record = $this->getRecord();
        $persona = $record->persona;

        $data['tipo_persona'] = $persona->tipo_persona;
        $data['persona'] = [
            'primer_nombre' => $persona->primer_nombre,
            'segundo_nombre' => $persona->segundo_nombre,
            'pais_id' => $persona->pais_id,
            'telefono' => $persona->telefono,
            'direccion' => $persona->direccion,
        ];

        if ($persona->tipo_persona === 'natural' && $persona->personaNatural) {
            $data['personaNatural'] = $persona->personaNatural->toArray();
        } elseif ($persona->tipo_persona === 'juridica' && $persona->personaJuridica) {
            $data['personaJuridica'] = $persona->personaJuridica->toArray();
        }

        return $data;
    }

    /** @param Proveedor $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $personaData = $data['persona'] ?? [];
        $tipoPersona = $data['tipo_persona'] ?? $record->persona->tipo_persona;

        $record->persona->update($personaData);
        $record->persona->update(['tipo_persona' => $tipoPersona]);

        if ($tipoPersona === 'natural') {
            $record->persona?->personaJuridica()?->delete();
            $naturalData = $data['personaNatural'] ?? [];
            if ($record->persona->personaNatural) {
                $record->persona->personaNatural->update($naturalData);
            } else {
                $record->persona->personaNatural()->create($naturalData);
            }
        } else {
            $record->persona?->personaNatural()?->delete();
            $juridicaData = $data['personaJuridica'] ?? [];
            if ($record->persona->personaJuridica) {
                $record->persona->personaJuridica->update($juridicaData);
            } else {
                $record->persona->personaJuridica()->create($juridicaData);
            }
        }

        $proveedorData = array_diff_key($data, ['persona' => [], 'personaNatural' => [], 'personaJuridica' => []]);

        $record->update($proveedorData);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
