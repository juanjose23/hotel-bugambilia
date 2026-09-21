<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Schemas;

use App\Filament\Pages\Reservas\CheckOutPage;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

final class CheckOutHabitacionSchema
{
    /**
     * @return array<int, Component>
     */
    public static function make(CheckOutPage $page): array
    {
        return [
            Section::make('3. Inspección de Habitación & Devolución de Llaves')
                ->icon('heroicon-o-key')
                ->description('Recepción de tarjetas/llaves entregadas y revisión de condiciones de salida.')
                ->collapsible()
                ->columns(2)
                ->schema([
                    TextEntry::make('estado_habitacion')
                        ->label('Estado actual')
                        ->state(fn (): string => $page->estancia?->habitacion?->estado?->getLabel() ?? '-')
                        ->badge()
                        ->color(fn (): string => $page->estancia?->habitacion?->estado?->getColor() ?? 'gray'),
                    TextEntry::make('destino_habitacion')
                        ->label('Destino posterior')
                        ->state('Sucia / pendiente de limpieza')
                        ->badge()
                        ->color('warning'),
                    TextInput::make('llaves_devueltas')
                        ->label('Llaves / tarjetas devueltas')
                        ->integer()
                        ->minValue(0)
                        ->default(fn (): int => $page->estancia->cantidad_llaves ?? 1)
                        ->live()
                        ->required(),
                    Toggle::make('autorizar_llaves_pendientes')
                        ->label('Autorizar llaves pendientes')
                        ->helperText('Debe usarse solo con autorización de recepción.')
                        ->live(),
                    Toggle::make('habitacion_inspeccionada')
                        ->label('Habitación inspeccionada')
                        ->live(),
                    Toggle::make('danos_reportados')
                        ->label('Daños o incidencias reportadas')
                        ->live(),
                    Textarea::make('observaciones')
                        ->label('Observaciones de salida')
                        ->placeholder('Inspección, llaves, incidencias, notas de recepción...')
                        ->maxLength(2000)
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }
}
