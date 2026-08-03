<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Espacio;

use App\Enums\Reservas\TipoReserva;
use App\Filament\Shared\Forms\EspacioSelect;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class EsquemaReservaEspacio
{
    /**
     * @return array<int, Section>
     */
    public static function make(): array
    {
        return [
            Section::make('Horario y Capacidad del Espacio / Área')
                ->columnSpanFull()
                ->icon(Heroicon::Clock)
                ->columns(3)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::PAQUETE->value)
                ->schema([
                    DatePicker::make('fecha_check_in')
                        ->label('Fecha de Uso / Reservación')
                        ->prefixIcon(Heroicon::CalendarDays)
                        ->suffixIcon('heroicon-m-chevron-down')
                        ->native(false)
                        ->closeOnDateSelection()
                        ->firstDayOfWeek(1)
                        ->displayFormat('d/m/Y')
                        ->minDate(now('America/Managua')->startOfDay())
                        ->required()
                        ->default(fn () => now('America/Managua'))
                        ->disabledOn('edit')
                        ->columnSpan(1),

                    TimePicker::make('hora_reserva')
                        ->label('Hora de Inicio (Nicaragua)')
                        ->prefixIcon(Heroicon::Clock)
                        ->suffixIcon('heroicon-m-chevron-down')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->default(fn (): string => now('America/Managua')->format('H:i'))
                        ->required()
                        ->disabledOn('edit')
                        ->columnSpan(1),

                    TextInput::make('adultos')
                        ->label('Cantidad de Asistentes')
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->required()
                        ->disabledOn('edit')
                        ->columnSpan(1),
                ]),

            Section::make('Asignación de Espacio o Área del Hotel')
                ->columnSpanFull()
                ->icon(Heroicon::Map)
                ->columns(2)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::PAQUETE->value)
                ->schema([
                    EspacioSelect::make(column: 'espacio_id', soloReservables: true)
                        ->required(fn ($get): bool => $get('tipo_reserva') === TipoReserva::PAQUETE->value)
                        ->columnSpan(1),
                ]),
        ];
    }
}
