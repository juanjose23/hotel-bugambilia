<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ResumenReserva
{
    public static function make(): Section
    {
        return Section::make('Resumen de la Reserva')
            ->columnSpanFull()
            ->icon(Heroicon::ClipboardDocumentList)
            ->columns(3)
            ->schema([
                TextEntry::make('codigo_reserva')
                    ->label('Código')
                    ->badge()
                    ->color('primary'),

                TextEntry::make('nombre_cliente')
                    ->label('Cliente'),

                TextEntry::make('telefono_cliente')
                    ->label('Teléfono')
                    ->placeholder('—'),

                TextEntry::make('email_cliente')
                    ->label('Email')
                    ->placeholder('—'),

                TextEntry::make('tipo_reserva')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon()),

                TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon()),

                TextEntry::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—'),

                TextEntry::make('espacio.nombre')
                    ->label('Espacio')
                    ->placeholder('—'),

                TextEntry::make('fecha_check_in')
                    ->label('Check-In')
                    ->date('d/m/Y'),

                TextEntry::make('fecha_check_out')
                    ->label('Check-Out')
                    ->date('d/m/Y')
                    ->placeholder('—'),

                TextEntry::make('adultos')
                    ->label('Adultos')
                    ->numeric(),

                TextEntry::make('ninos')
                    ->label('Niños')
                    ->numeric(),

                TextEntry::make('total')
                    ->label('Total')
                    ->money('NIO'),

                IconEntry::make('solicita_cuenta')
                    ->label('Cuenta solicitada')
                    ->boolean(),

                TextEntry::make('notas')
                    ->label('Notas')
                    ->placeholder('Sin notas')
                    ->columnSpanFull(),
            ]);
    }
}
