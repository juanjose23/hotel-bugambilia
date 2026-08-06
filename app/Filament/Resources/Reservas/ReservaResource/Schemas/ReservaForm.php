<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Schemas;

use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\DatosClienteSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\InformacionGeneralSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\NotasReservaSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\ResumenFinancieroYAbonoSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Espacio\EsquemaReservaEspacio;
use App\Filament\Resources\Reservas\Schemas\Reserva\Habitacion\EsquemaReservaHabitacion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Restaurante\EsquemaReservaRestaurante;
use App\Filament\Resources\Reservas\Schemas\Reserva\SelectorServiciosAdicionales;
use App\Filament\Resources\Reservas\Schemas\Reserva\Servicio\EsquemaReservaServicio;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Schema;

class ReservaForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Callout::make('Pago del 50 % pendiente')
                    ->description('Esta reserva aún no está pagada. Tiene que abonar el 50 % del total antes de confirmar la reserva.')
                    ->warning()
                    ->visibleOn('edit')
                    ->visible(fn ($record): bool => $record !== null && (float) $record->saldo > 0),

                InformacionGeneralSeccion::make(),
                DatosClienteSeccion::make(),
                ...EsquemaReservaHabitacion::make(),
                ...EsquemaReservaRestaurante::make(),
                ...EsquemaReservaEspacio::make(),
                ...EsquemaReservaServicio::make(),
                SelectorServiciosAdicionales::make(),
                ResumenFinancieroYAbonoSeccion::make(),
                NotasReservaSeccion::make(),
            ]);
    }
}
