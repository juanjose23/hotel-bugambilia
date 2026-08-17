<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Habitacion;

use App\BusinessLogic\Reservas\Data\ConsultarDisponibilidadHabitacionData;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Queries\Reservas\ConsultarHabitacionesDisponibles;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                ->visible(fn ($get): bool => in_array($get('tipo_reserva'), [TipoReserva::HABITACION->value, TipoReserva::PAQUETE->value], true))
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
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('adultos')
                        ->label('Adultos')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('ninos')
                        ->label('Niños')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live()
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
                ->description('Seleccione la habitación principal y las adicionales; el sistema solo mostrará habitaciones disponibles y compatibles con la capacidad y las fechas de la reserva.')
                ->columns(2)
                ->visible(fn ($get): bool => in_array($get('tipo_reserva'), [TipoReserva::HABITACION->value, TipoReserva::PAQUETE->value], true))
                ->schema([
                    Select::make('habitacion_id')
                        ->label('Habitación Principal')
                        ->placeholder('Seleccione habitación principal')
                        ->options(function (Get $get): array {
                            return self::opcionesHabitaciones($get);
                        })
                        ->searchable()
                        ->preload()
                        ->required(fn ($get): bool => $get('tipo_reserva') === TipoReserva::HABITACION->value)
                        ->native(false)
                        ->live()
                        ->columnSpan(1),

                    TextEntry::make('servicios_incluidos_info')
                        ->label('Servicios e Inclusiones de la Habitación')
                        ->state(function (Get $get): string {
                            $habitacionId = self::enteroOpcional($get('habitacion_id'));
                            if ($habitacionId <= 0) {
                                return 'Seleccione una habitación principal para consultar los servicios incluidos.';
                            }

                            $habitacion = Habitacion::query()
                                ->with(['servicioAsignaciones.servicio', 'categoria'])
                                ->find($habitacionId);

                            if (! $habitacion instanceof Habitacion) {
                                return 'Habitación no encontrada.';
                            }

                            $servicios = $habitacion->servicioAsignaciones
                                ->map(fn ($sa) => $sa->servicio?->nombre)
                                ->filter()
                                ->values()
                                ->all();

                            if ($servicios === []) {
                                return 'Habitación estándar sin servicios específicos adicionales asignados.';
                            }

                            return implode(' • ', $servicios);
                        })
                        ->columnSpan(1),

                    Repeater::make('habitaciones_adicionales')
                        ->label('Habitaciones adicionales')
                        ->addActionLabel('Agregar habitación adicional')
                        ->defaultItems(0)
                        ->collapsible()
                        ->live()
                        ->columnSpanFull()
                        ->columns(1)
                        ->schema([
                            Select::make('habitacion_id')
                                ->label('Habitación Adicional')
                                ->placeholder('Seleccione habitación adicional')
                                ->options(function (Get $get): array {
                                    $principalId = self::enteroOpcional($get('../../habitacion_id'));

                                    return self::opcionesHabitaciones($get, $principalId);
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)
                                ->live()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        ]),
                ]),

            Section::make('Registro de Acompañantes / Huéspedes')
                ->columnSpanFull()
                ->icon(Heroicon::UserGroup)
                ->description('Registre los nombres e identificación de los acompañantes')
                ->visible(fn ($get): bool => in_array($get('tipo_reserva'), [TipoReserva::HABITACION->value, TipoReserva::PAQUETE->value], true))
                ->schema([
                    Repeater::make('acompanantes')
                        ->hiddenLabel()
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

    /**
     * @return array<int, string>
     */
    private static function opcionesHabitaciones(Get $get, ?int $excluirHabitacionId = null): array
    {
        $fechaCheckIn = self::carbonoOpcional($get('fecha_check_in'));
        $fechaCheckOut = self::carbonoOpcional($get('fecha_check_out'));
        $adultos = self::enteroOpcional($get('adultos'), 1);
        $ninos = self::enteroOpcional($get('ninos'), 0);
        $habitacionSeleccionadaId = self::enteroOpcional($get('habitacion_id'));

        if ($fechaCheckIn !== null && $fechaCheckOut !== null && $fechaCheckOut->greaterThan($fechaCheckIn)) {
            $data = new ConsultarDisponibilidadHabitacionData(
                fechaCheckIn: $fechaCheckIn,
                fechaCheckOut: $fechaCheckOut,
                adultos: $adultos,
                ninos: $ninos,
            );

            $habitaciones = app(ConsultarHabitacionesDisponibles::class)->ejecutar($data);

            if ($excluirHabitacionId !== null) {
                $habitaciones = $habitaciones->reject(
                    fn (RecursoReservable $recurso): bool => (int) ($recurso->habitacion->id ?? 0) === $excluirHabitacionId,
                );
            }

            $opciones = $habitaciones
                ->mapWithKeys(static function (RecursoReservable $recurso): array {
                    $habitacion = $recurso->habitacion;

                    if (! $habitacion instanceof Habitacion) {
                        return [];
                    }

                    return [$habitacion->id => self::etiquetaHabitacion($habitacion, $recurso)];
                })
                ->all();

            return self::agregarHabitacionSeleccionada($opciones, $habitacionSeleccionadaId);
        }

        $query = Habitacion::query()
            ->with(['categoria', 'reservable'])
            ->where('estado', EstadoEspacio::Disponible->value);

        if ($excluirHabitacionId !== null) {
            $query->where('id', '!=', $excluirHabitacionId);
        }

        $opciones = $query
            ->orderBy('categoria_id')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(static function (Habitacion $habitacion): array {
                return [$habitacion->id => self::etiquetaHabitacion($habitacion)];
            })
            ->all();

        return self::agregarHabitacionSeleccionada($opciones, $habitacionSeleccionadaId);
    }

    /**
     * @param  array<int, string>  $opciones
     * @return array<int, string>
     */
    private static function agregarHabitacionSeleccionada(array $opciones, int $habitacionId): array
    {
        if ($habitacionId <= 0 || array_key_exists($habitacionId, $opciones)) {
            return $opciones;
        }

        $habitacion = Habitacion::query()
            ->with(['categoria', 'reservable'])
            ->find($habitacionId);

        if (! $habitacion instanceof Habitacion) {
            return $opciones;
        }

        return [$habitacion->id => self::etiquetaHabitacion($habitacion).' · Seleccionada'] + $opciones;
    }

    private static function etiquetaHabitacion(Habitacion $habitacion, ?RecursoReservable $recurso = null): string
    {
        $categoria = $habitacion->categoria->nombre ?? 'Sin Categ.';
        $capacidad = $recurso->capacidad ?? $habitacion->reservable?->capacidad;
        $capacidadStr = is_numeric($capacidad) && (int) $capacidad > 0
            ? ' · Cap: '.(int) $capacidad.' pers.'
            : '';

        return "{$habitacion->nombre} ({$categoria}){$capacidadStr}";
    }

    private static function carbonoOpcional(mixed $valor): ?Carbon
    {
        if (! is_string($valor) || trim($valor) === '') {
            return null;
        }

        try {
            return Carbon::parse($valor, 'America/Managua');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function enteroOpcional(mixed $valor, int $porDefecto = 0): int
    {
        return is_numeric($valor) ? (int) $valor : $porDefecto;
    }
}
