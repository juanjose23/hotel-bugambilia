<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\CheckOut;

use App\Repository\Models\Reservas\Reserva;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class SeccionCuentaConsumoCheckOut
{
    public static function make(): Section
    {
        return Section::make('Cuenta de Consumo')
            ->icon('heroicon-o-banknotes')
            ->schema([
                TextEntry::make('saldo_cuenta')
                    ->label('Saldo pendiente')
                    ->state(function ($record, $livewire): string {
                        /** @var Reserva|null $reserva */
                        $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);
                        $saldo = $reserva !== null ? (float) ($reserva->estancia->cuenta->saldo ?? 0.0) : 0.0;

                        return '$'.number_format($saldo, 2);
                    })
                    ->color(fn ($state): string => str_contains((string) $state, '-') ? 'danger' : 'success')
                    ->icon(fn ($state): string => str_contains((string) $state, '-') ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),
            ]);
    }
}
