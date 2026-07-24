<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use Filament\Tables\Columns\TextColumn;

class InsigniaEstadoReserva
{
    public static function make(string $column = 'estado'): TextColumn
    {
        return TextColumn::make($column)
            ->label('Estado')
            ->badge()
            ->color(fn ($state) => $state?->getColor() ?? 'gray')
            ->icon(fn ($state) => $state?->getIcon())
            ->sortable();
    }
}
