<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\Reservas\TipoHuesped;
use App\Repository\Models\Reservas\Reserva;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ResumenCapacidad
{
    public static function make(): Section
    {
        return Section::make('Capacidad')
            ->columnSpanFull()
            ->icon(Heroicon::Users)
            ->columns(3)
            ->schema([
                TextEntry::make('adultos')
                    ->label('Adultos declarados')
                    ->numeric(),

                TextEntry::make('ninos')
                    ->label('Niños declarados')
                    ->numeric(),

                TextEntry::make('detalles')
                    ->label('Huéspedes registrados')
                    ->state(function ($record): string {
                        /** @var Reserva $record */
                        $huespedes = $record->huespedes;
                        $adultos = $huespedes->where('tipo_huesped', TipoHuesped::ADULTO)->count();
                        $ninos = $huespedes->where('tipo_huesped', TipoHuesped::NINO)->count();
                        $infantes = $huespedes->where('tipo_huesped', TipoHuesped::INFANTE)->count();

                        return "{$adultos} adultos, {$ninos} niños, {$infantes} infantes ({$huespedes->count()} total)";
                    }),
            ]);
    }
}
