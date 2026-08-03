<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\Reservas\TipoReserva;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;

class CamposPeriodoReserva
{
    /** @return array<int, DatePicker|TimePicker|TextInput> */
    public static function make(int $columnSpan = 1): array
    {
        return [
            DatePicker::make('fecha_check_in')
                ->label(fn ($get): string => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value ? 'Fecha de Reservación' : 'Fecha Check-In / Entrada')
                ->required()
                ->default(now())
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            DatePicker::make('fecha_check_out')
                ->label('Fecha Check-Out (Salida)')
                ->nullable()
                ->visible(fn ($get): bool => $get('tipo_reserva') !== TipoReserva::RESTAURANTE->value)
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            TimePicker::make('hora_reserva')
                ->label('Hora de Reservación')
                ->default('13:00')
                ->required(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value || $get('tipo_reserva') === TipoReserva::SERVICIO->value)
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            TextInput::make('adultos')
                ->label(fn ($get): string => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value ? 'Total de Comensales' : 'Adultos')
                ->numeric()
                ->default(2)
                ->minValue(1)
                ->required()
                ->disabledOn('edit')
                ->columnSpan($columnSpan),

            TextInput::make('ninos')
                ->label('Niños')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->visible(fn ($get): bool => $get('tipo_reserva') !== TipoReserva::RESTAURANTE->value)
                ->disabledOn('edit')
                ->columnSpan($columnSpan),
        ];
    }
}
