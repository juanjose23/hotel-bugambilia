<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Comun;

use App\Filament\Shared\Forms\SelectorCliente;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class DatosClienteSeccion
{
    public static function make(): Section
    {
        return Section::make('Datos del Cliente')
            ->columnSpanFull()
            ->icon(Heroicon::User)
            ->columns(3)
            ->schema(SelectorCliente::make(columnSpan: 1));
    }
}
