<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Interactors\Reservas\ActualizarReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Cuentas\ObtenerCuentaReservaQuery;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @property Reserva $record
 */
class EditReserva extends EditRecord
{
    protected static string $resource = ReservaResource::class;

    protected ActualizarReserva $actualizarReserva;

    public function boot(ActualizarReserva $actualizarReserva): void
    {
        $this->actualizarReserva = $actualizarReserva;
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof Reserva) {
            throw new LogicException('El registro recibido no es una reserva.');
        }

        return $this->actualizarReserva->ejecutar($record, $data);
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;
        $detalles = $record->load('detalles.reservable')->detalles->whereNotNull('parent_id');

        $data['servicios_adicionales'] = $detalles
            ->filter(fn ($detalle) => $detalle->reservable?->servicio instanceof Servicio)
            ->map(fn ($detalle): array => [
                'servicio_id' => $detalle->reservable?->servicio?->id,
                'cantidad' => $detalle->cantidad,
            ])
            ->values()
            ->all();

        $data['espacios_adicionales'] = $detalles
            ->filter(fn ($detalle) => $detalle->reservable?->espacio instanceof Espacio)
            ->map(fn ($detalle): array => [
                'espacio_id' => $detalle->reservable?->espacio?->id,
                'cantidad' => 1,
            ])
            ->values()
            ->all();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $cuenta = app(ObtenerCuentaReservaQuery::class)->ejecutar((int) $this->record->id);

        return [
            Action::make('gestionarPagos')
                ->label((float) $this->record->saldo > 0 ? 'Registrar abono o pago' : 'Ver pagos')
                ->icon('heroicon-o-banknotes')
                ->color((float) $this->record->saldo > 0 ? 'warning' : 'success')
                ->visible($cuenta !== null)
                ->url($cuenta !== null ? CuentaResource::getUrl('view', ['record' => $cuenta]) : null),
        ];
    }
}
