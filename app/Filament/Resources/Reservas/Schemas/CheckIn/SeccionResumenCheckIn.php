<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\CheckIn;

use App\Repository\Models\Reservas\Reserva;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class SeccionResumenCheckIn
{
    public static function make(): Section
    {
        return Section::make('Datos de la Reserva')
            ->icon('heroicon-o-information-circle')
            ->schema([
                TextEntry::make('codigo_reserva')
                    ->label('Código')
                    ->state(function ($record, $livewire): string {
                        /** @var Reserva|null $reserva */
                        $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);

                        return $reserva !== null ? $reserva->codigo_reserva : '-';
                    }),

                TextEntry::make('nombre_cliente')
                    ->label('Cliente')
                    ->state(function ($record, $livewire): string {
                        /** @var Reserva|null $reserva */
                        $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);

                        return ($reserva !== null && $reserva->nombre_cliente !== null && $reserva->nombre_cliente !== '')
                            ? $reserva->nombre_cliente
                            : '-';
                    }),

                TextEntry::make('habitacion')
                    ->label('Habitación')
                    ->state(function ($record, $livewire): string {
                        /** @var Reserva|null $reserva */
                        $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);
                        if ($reserva === null) {
                            return '-';
                        }

                        return $reserva->habitacion->nombre ?? $reserva->espacio->nombre ?? '-';
                    }),

                TextEntry::make('fecha_check_in')
                    ->label('Check-In esperado')
                    ->state(function ($record, $livewire): string {
                        /** @var Reserva|null $reserva */
                        $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);

                        return $reserva !== null ? $reserva->fecha_check_in->format('d/m/Y') : '-';
                    }),

                TextEntry::make('fecha_check_out')
                    ->label('Check-Out esperado')
                    ->state(function ($record, $livewire): string {
                        /** @var Reserva|null $reserva */
                        $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);

                        return $reserva?->fecha_check_out !== null ? $reserva->fecha_check_out->format('d/m/Y') : '-';
                    }),
            ])
            ->columns(3);
    }
}
