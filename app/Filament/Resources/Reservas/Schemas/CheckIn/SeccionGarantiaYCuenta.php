<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\CheckIn;

use App\Repository\Models\Reservas\Reserva;
use App\Support\MonedaHelper;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

/**
 * Sección de Paso 4 — Garantía y Cuenta de Consumo del Check-In.
 */
class SeccionGarantiaYCuenta
{
    public static function make(): Group
    {
        return Group::make([
            Section::make('Garantía y Cuenta de Consumo')
                ->icon('heroicon-o-credit-card')
                ->description('Configure la garantía de estancia y, si aplica, la cuenta de consumo del huésped.')
                ->schema([
                    Toggle::make('abrir_cuenta')
                        ->label('Abrir cuenta de consumo')
                        ->helperText('El huésped podrá cargar consumos (restaurante, bar, lavandería, etc.) a su habitación.')
                        ->default(function ($record, $livewire): bool {
                            /** @var Reserva|null $reserva */
                            $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);

                            if ($reserva === null) {
                                return false;
                            }

                            return (bool) ($reserva->solicita_cuenta || $reserva->cuentas()->exists());
                        })
                        ->live(),

                    TextInput::make('limite_cuenta')
                        ->label('Límite autorizado de la cuenta')
                        ->numeric()
                        ->prefix(fn ($record, $livewire): string => MonedaHelper::simbolo($record->moneda ?? $livewire->reserva?->moneda))
                        ->minValue(0)
                        ->helperText('Monto máximo permitido para cargos a la cuenta.')
                        ->visible(fn (callable $get): bool => (bool) $get('abrir_cuenta')),

                    TextInput::make('cantidad_llaves')
                        ->label('Llaves entregadas al huésped')
                        ->integer()
                        ->minValue(1)
                        ->maxValue(10)
                        ->default(1)
                        ->required()
                        ->suffix('llave(s)'),

                    Textarea::make('observaciones')
                        ->label('Observaciones de entrada')
                        ->placeholder('Estado de la habitación, solicitudes especiales, notas...')
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
