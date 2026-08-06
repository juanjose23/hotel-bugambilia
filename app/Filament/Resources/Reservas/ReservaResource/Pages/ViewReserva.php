<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Resources\Reservas\Schemas\Reserva\AccionesReserva;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Queries\Cuentas\ObtenerCuentaReservaQuery;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReserva extends ViewRecord
{
    protected static string $resource = ReservaResource::class;

    protected static ?string $title = 'Detalle de Reserva';

    protected function getHeaderActions(): array
    {
        return [
            ...AccionesReserva::make(),

            CobrarCuentaAction::makeFromResolver(
                resolverCuenta: fn (): ?Cuenta => app(ObtenerCuentaReservaQuery::class)->ejecutar(
                    is_numeric($this->getRecord()->getKey()) ? (int) $this->getRecord()->getKey() : 0
                ),
                onSuccess: function (Cuenta $cuenta): void {
                    $this->record = $this->getRecord()->refresh();
                },
            )
                ->name('cobrarReserva')
                ->label('Cobrar reserva'),

            Actions\EditAction::make(),
        ];
    }
}
