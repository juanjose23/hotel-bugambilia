<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Repository\Models\Reservas\Reserva;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class IndicadorDocumentacion
{
    public static function make(): Section
    {
        return Section::make('Documentación de Huéspedes')
            ->columnSpanFull()
            ->icon(Heroicon::DocumentCheck)
            ->schema([
                TextEntry::make('detalles')
                    ->hiddenLabel()
                    ->state(function ($record): string {
                        /** @var Reserva $record */
                        $huespedes = $record->huespedes;
                        $conIdentificacion = $huespedes->whereNotNull('identificacion')->where('identificacion', '!=', '')->count();
                        $total = $huespedes->count();

                        return "{$conIdentificacion}/{$total} huéspedes con identificación registrada";
                    }),
            ]);
    }
}
