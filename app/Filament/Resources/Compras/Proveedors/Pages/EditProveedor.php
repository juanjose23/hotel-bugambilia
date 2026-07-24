<?php

namespace App\Filament\Resources\Compras\Proveedors\Pages;

use App\Filament\Resources\Compras\Proveedors\ProveedorResource;
use App\Interactors\Compras\Proveedores\ActualizarProveedor;
use App\Repository\Models\Compras\Proveedor;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProveedor extends EditRecord
{
    protected ActualizarProveedor $actualizarProveedor;

    public function boot(ActualizarProveedor $actualizarProveedor): void
    {
        $this->actualizarProveedor = $actualizarProveedor;
    }

    protected static string $resource = ProveedorResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Proveedor $record */
        $record = $this->getRecord();
        $record->loadMissing('persona.personaNatural', 'persona.personaJuridica');
        $persona = $record->persona;

        if ($persona) {
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
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Proveedor $record */
        return $this->actualizarProveedor->ejecutar($record, $data);
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
