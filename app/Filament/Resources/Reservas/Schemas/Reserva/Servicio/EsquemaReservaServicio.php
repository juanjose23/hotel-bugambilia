<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Servicio;

use App\Enums\Reservas\TipoReserva;
use App\Filament\Shared\Forms\ServicioSelect;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class EsquemaReservaServicio
{
    /**
     * @return array<int, Section>
     */
    public static function make(): array
    {
        return [
            Section::make('Horario y Detalles del Servicio Especial')
                ->columnSpanFull()
                ->icon(Heroicon::CheckBadge)
                ->columns(['default' => 1, 'sm' => 2, 'md' => 3])
                ->visible(fn ($get): bool => in_array($get('tipo_reserva'), [TipoReserva::SERVICIO->value, TipoReserva::PAQUETE->value], true))
                ->schema([
                    DatePicker::make('fecha_check_in')
                        ->label('Fecha del Servicio')
                        ->prefixIcon(Heroicon::CalendarDays)
                        ->suffixIcon('heroicon-m-chevron-down')
                        ->native(false)
                        ->closeOnDateSelection()
                        ->firstDayOfWeek(1)
                        ->displayFormat('d/m/Y')
                        ->minDate(now('America/Managua')->startOfDay())
                        ->required()
                        ->default(fn () => now('America/Managua'))
                        ->columnSpan(1),

                    TimePicker::make('hora_reserva')
                        ->label('Hora Programada (Nicaragua)')
                        ->prefixIcon(Heroicon::Clock)
                        ->suffixIcon('heroicon-m-chevron-down')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->default(fn (): string => now('America/Managua')->format('H:i'))
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('adultos')
                        ->label('Cantidad de Personas')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required()
                        ->columnSpan(1),
                ]),

            Section::make('Servicio Asignado')
                ->columnSpanFull()
                ->icon(Heroicon::CheckBadge)
                ->columns(2)
                ->visible(fn ($get): bool => in_array($get('tipo_reserva'), [TipoReserva::SERVICIO->value, TipoReserva::PAQUETE->value], true))
                ->schema([
                    ServicioSelect::make(column: 'servicio_id')
                        ->required(fn ($get): bool => $get('tipo_reserva') === TipoReserva::SERVICIO->value)
                        ->live()
                        ->columnSpan(1),
                ]),
        ];
    }
}
