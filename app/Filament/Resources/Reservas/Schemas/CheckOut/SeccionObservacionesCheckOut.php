<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\CheckOut;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class SeccionObservacionesCheckOut
{
    public static function make(): Section
    {
        return Section::make('Cierre de Estancia & Devolución de Llaves')
            ->icon('heroicon-o-key')
            ->columns(2)
            ->schema([
                TextInput::make('llaves_devueltas')
                    ->label('Llaves devueltas')
                    ->numeric()
                    ->minValue(0)
                    ->default(1)
                    ->required(),

                Toggle::make('autorizar_llaves_pendientes')
                    ->label('Autorizar llaves pendientes')
                    ->helperText('Permite omitir la entrega completa de llaves bajo responsabilidad de recepción.')
                    ->default(false),

                Toggle::make('credito_autorizado')
                    ->label('Autorizar crédito corporativo / cuenta abierta')
                    ->helperText('Excepción para cuentas corporativas con crédito aprobado previo.')
                    ->default(false)
                    ->columnSpanFull(),

                Textarea::make('observaciones')
                    ->label('Observaciones de salida')
                    ->maxLength(2000)
                    ->placeholder('Notas sobre la inspección de habitación, llaves y salida del huésped...')
                    ->columnSpanFull(),
            ]);
    }
}
