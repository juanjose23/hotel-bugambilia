<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Repository\Models\Reservas\Reserva;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class SelectorHuespedTitular
{
    public static function make(): Section
    {
        return Section::make('Titular de la Reserva')
            ->columnSpanFull()
            ->icon(Heroicon::UserCircle)
            ->schema([
                Select::make('titular_id')
                    ->label('Seleccionar huésped titular')
                    ->options(function ($record) {
                        /** @var Reserva|null $record */
                        if ($record === null) {
                            return [];
                        }

                        return $record->huespedes
                            ->mapWithKeys(fn ($h) => [$h->id => "{$h}"])
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->helperText('Seleccione cuál de los huéspedes registrados es el titular de la reserva.'),
            ]);
    }
}
