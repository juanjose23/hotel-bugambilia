<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Repository\Models\Reservas\Reserva;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class LineaTiempoReserva
{
    public static function make(): Section
    {
        return Section::make('Línea de Tiempo')
            ->columnSpanFull()
            ->icon(Heroicon::Clock)
            ->schema([
                TextEntry::make('historialEstados')
                    ->hiddenLabel()
                    ->badge()
                    ->formatStateUsing(function ($state, $record): string {
                        /** @var Reserva $record */
                        if ($state === null) {
                            return 'Creación';
                        }

                        $etiqueta = is_object($state) && method_exists($state, 'label')
                            ? (string) $state->label()
                            : (string) $state;

                        $historial = $record->historialEstados->where('estado_nuevo', $state)->first();

                        return $historial?->motivo !== null
                            ? "{$etiqueta} — {$historial->motivo}"
                            : $etiqueta;
                    })
                    ->columnSpanFull(),
            ]);
    }
}
