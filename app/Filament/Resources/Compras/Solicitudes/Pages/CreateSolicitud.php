<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Solicitud;
use App\Services\Compras\NotificadorCompras;
use App\UseCases\Compras\Solicitudes\Mutations\GenerarCodigoSolicitud;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSolicitud extends CreateRecord
{
    protected static string $resource = SolicitudResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $colaborador = Colaborador::where('persona_id', auth()->user()?->persona_id)->first();

        if ($colaborador === null) {
            throw new \RuntimeException('El usuario actual no tiene un colaborador asignado.');
        }

        $data['colaborador_id'] = $colaborador->id;

        $departamentoSolicitanteId = is_numeric($data['departamento_solicitante_id'] ?? null) ? intval($data['departamento_solicitante_id']) : 0;
        $data['codigo'] = app(GenerarCodigoSolicitud::class)->ejecutar($departamentoSolicitanteId);

        $data['estado'] = EstadoSolicitud::Borrador->value;

        return $data;
    }

    /** @return array<int, Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = (array) ($data['items'] ?? []);
        unset($data['items']);

        /** @var Solicitud $record */
        $record = $this->getModel()::create($data);

        if (! empty($items)) {
            $record->items()->createMany($items);
        }

        app(NotificadorCompras::class)->solicitudCreada($record);

        return $record;
    }
}
