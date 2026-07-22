<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Interactors\Compras\Solicitudes\CrearSolicitudConItems;
use App\Interactors\Compras\Solicitudes\GenerarCodigoSolicitud;
use App\Repository\Queries\Compras\Shared\ObtenerColaboradorDeSesion;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSolicitud extends CreateRecord
{
    protected ObtenerColaboradorDeSesion $colaboradorDeSesion;

    protected CrearSolicitudConItems $crearSolicitudConItems;

    protected GenerarCodigoSolicitud $generarCodigoSolicitud;

    public function boot(ObtenerColaboradorDeSesion $colaboradorDeSesion, CrearSolicitudConItems $crearSolicitudConItems, GenerarCodigoSolicitud $generarCodigoSolicitud): void
    {
        $this->colaboradorDeSesion = $colaboradorDeSesion;
        $this->crearSolicitudConItems = $crearSolicitudConItems;
        $this->generarCodigoSolicitud = $generarCodigoSolicitud;
    }

    protected static string $resource = SolicitudResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $colaborador = $this->colaboradorDeSesion->ejecutar();

        if ($colaborador === null) {
            return $data;
        }

        $data['colaborador_id'] = $colaborador->id;

        $departamentoSolicitanteId = is_numeric($data['departamento_solicitante_id'] ?? null) ? intval($data['departamento_solicitante_id']) : 0;
        $data['codigo'] = $this->generarCodigoSolicitud->ejecutar($departamentoSolicitanteId);

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

        return $this->crearSolicitudConItems->ejecutar($data, $items);
    }
}
