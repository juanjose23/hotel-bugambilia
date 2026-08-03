<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Habitacion;

use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class EsquemaReservaHabitacion
{
    /**
     * @return array<int, Section>
     */
    public static function make(): array
    {
        return [
            Section::make('Periodo de Estancia (Noches) y Capacidad')
                ->columnSpanFull()
                ->icon(Heroicon::CalendarDays)
                ->columns(3)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::HABITACION->value)
                ->schema([
                    DatePicker::make('fecha_check_in')
                        ->label('Fecha Check-In (Entrada)')
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
                        ->live()
                        ->columnSpan(1),

                    DatePicker::make('fecha_check_out')
                        ->label('Fecha Check-Out (Salida)')
                        ->prefixIcon(Heroicon::CalendarDays)
                        ->suffixIcon('heroicon-m-chevron-down')
                        ->native(false)
                        ->closeOnDateSelection()
                        ->firstDayOfWeek(1)
                        ->displayFormat('d/m/Y')
                        ->minDate(now()->startOfDay())
                        ->required()
                        ->disabledOn('edit')
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('adultos')
                        ->label('Adultos')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required()
                        ->disabledOn('edit')
                        ->columnSpan(1),

                    TextInput::make('ninos')
                        ->label('Niños')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->disabledOn('edit')
                        ->columnSpan(1),

                    Toggle::make('solicita_cuenta')
                        ->label('Solicita cuenta de consumo')
                        ->helperText('La cuenta será validada y abierta por recepción durante el check-in.')
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('limite_cuenta_solicitado')
                        ->label('Límite solicitado')
                        ->numeric()
                        ->prefix('C$')
                        ->minValue(0)
                        ->visible(fn ($get): bool => (bool) $get('solicita_cuenta'))
                        ->columnSpan(1),
                ]),

            Section::make('Asignación de Habitación')
                ->columnSpanFull()
                ->icon(Heroicon::Home)
                ->columns(2)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::HABITACION->value)
                ->schema([
                    Select::make('habitacion_id')
                        ->label('Habitación Asignada')
                        ->placeholder('Seleccione habitación')
                        ->options(function (): array {
                            return Habitacion::with('categoria')->get()->mapWithKeys(function ($h): array {
                                $cat = $h->categoria->nombre ?? 'Sin Categ.';

                                return [$h->id => "{$h->nombre} ({$cat})"];
                            })->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->required(fn ($get): bool => $get('tipo_reserva') === TipoReserva::HABITACION->value)
                        ->disabledOn('edit')
                        ->native(false)
                        ->live()
                        ->columnSpan(1),
                ]),

            Section::make('Registro de Acompañantes / Huéspedes')
                ->columnSpanFull()
                ->icon(Heroicon::UserGroup)
                ->description('Registre los nombres e identificación de los acompañantes')
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::HABITACION->value)
                ->schema([
                    Repeater::make('acompanantes')
                        ->hiddenLabel()
                        ->disabledOn('edit')
                        ->columns(3)
                        ->itemLabel(fn (array $state): string => (string) ($state['nombre'] ?? 'Acompañante'))
                        ->schema([
                            TextInput::make('nombre')
                                ->label('Nombre Completo')
                                ->placeholder('Ej. María Pérez')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('identificacion')
                                ->label('DNI / Cédula / Pasaporte')
                                ->placeholder('Ej. 001-010190-0001A')
                                ->columnSpan(1),

                            Select::make('tipo')
                                ->label('Categoría / Edad')
                                ->options([
                                    'adulto' => 'Adulto',
                                    'nino' => 'Niño',
                                    'infante' => 'Infante',
                                ])
                                ->default('adulto')
                                ->native(false)
                                ->columnSpan(1),
                        ]),
                ]),
        ];
    }
}
