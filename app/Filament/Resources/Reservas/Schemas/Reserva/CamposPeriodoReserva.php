<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

class CamposPeriodoReserva
{
    /** @return array<int, DatePicker|TextInput> */
    public static function make(int $columnSpan = 1): array
    {
        return [
            DatePicker::make('fecha_check_in')
                ->label('Fecha Check-In / Reservación')
                ->required()
                ->default(now())
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            DatePicker::make('fecha_check_out')
                ->label('Fecha Check-Out (Salida)')
                ->nullable()
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            TextInput::make('hora_reserva')
                ->label('Hora de Reservación')
                ->placeholder('Ej. 19:00')
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            TextInput::make('adultos')
                ->label('Adultos')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            TextInput::make('ninos')
                ->label('Niños')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->disabledOn('edit')
                ->columnSpan($columnSpan),
        ];
    }
}
