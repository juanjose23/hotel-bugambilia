<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Servicios\Servicio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class SelectorServiciosAdicionales
{
    public static function make(): Section
    {
        return Section::make('Recursos adicionales')
            ->columnSpanFull()
            ->icon(Heroicon::PlusCircle)
            ->description('Agregue los servicios y espacios que forman parte de esta misma reserva.')
            ->columns(1)
            ->schema([
                Repeater::make('servicios_adicionales')
                    ->label('Servicios adicionales')
                    ->addActionLabel('Agregar servicio')
                    ->defaultItems(0)
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2, 'md' => 6])
                    ->schema([
                        Select::make('servicio_id')
                            ->label('Servicio')
                            ->placeholder('Seleccione un servicio')
                            ->options(fn () => Servicio::query()
                                ->activos()
                                ->orderBy('nombre')
                                ->get()
                                ->mapWithKeys(function (Servicio $servicio): array {
                                    $precioVal = $servicio->precios()->latest()->first()->precio ?? $servicio->precio_base ?? 0.0;
                                    $precioStr = number_format((float) $precioVal, 2);

                                    return [$servicio->id => "{$servicio->nombre} (C$ {$precioStr})"];
                                })
                                ->all())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->validationMessages([
                                'required' => 'Seleccione el servicio que desea agregar.',
                            ])
                            ->live()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->columnSpan(['default' => 1, 'sm' => 2, 'md' => 3]),

                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->validationMessages([
                                'required' => 'Indique la cantidad del servicio.',
                                'integer' => 'La cantidad debe ser un número entero.',
                                'min' => 'La cantidad mínima es 1.',
                            ])
                            ->live(onBlur: true)
                            ->columnSpan(['default' => 1, 'sm' => 1, 'md' => 1]),

                        TextEntry::make('precio_subtotal_servicio')
                            ->label('Precio Estimado')
                            ->state(function (Get $get): string {
                                $servicioId = $get('servicio_id');
                                $cantidad = is_numeric($get('cantidad')) ? (int) $get('cantidad') : 1;

                                if (! is_numeric($servicioId)) {
                                    return '—';
                                }

                                $servicio = Servicio::find((int) $servicioId);
                                if (! $servicio instanceof Servicio) {
                                    return '—';
                                }

                                $precioVal = (float) ($servicio->precios()->latest()->first()->precio ?? $servicio->precio_base ?? 0.0);
                                $subtotal = $precioVal * $cantidad;

                                return sprintf('C$ %s c/u · Total: C$ %s', number_format($precioVal, 2), number_format($subtotal, 2));
                            })
                            ->columnSpan(['default' => 1, 'sm' => 1, 'md' => 2]),

                        DatePicker::make('fecha_servicio')
                            ->label('Fecha Programada (Spa/Actividades)')
                            ->prefixIcon(Heroicon::CalendarDays)
                            ->suffixIcon('heroicon-m-chevron-down')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->firstDayOfWeek(1)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(['default' => 1, 'sm' => 1, 'md' => 3]),

                        TimePicker::make('hora_servicio')
                            ->label('Hora Programada')
                            ->prefixIcon(Heroicon::Clock)
                            ->suffixIcon('heroicon-m-chevron-down')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('H:i')
                            ->columnSpan(['default' => 1, 'sm' => 1, 'md' => 3]),
                    ]),

                Repeater::make('espacios_adicionales')
                    ->label('Espacios adicionales')
                    ->addActionLabel('Agregar espacio')
                    ->defaultItems(0)
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        Select::make('espacio_id')
                            ->label('Espacio')
                            ->placeholder('Seleccione un espacio')
                            ->options(fn (Get $get): array => self::opcionesEspaciosReservables($get))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->validationMessages([
                                'required' => 'Seleccione la mesa o espacio adicional que se unirá a la reserva.',
                            ])
                            ->live()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->columnSpan(['default' => 1, 'sm' => 2]),

                        Hidden::make('cantidad')->default(1),
                    ]),
            ]);
    }

    /** @return array<int, string> */
    private static function opcionesEspaciosReservables(Get $get): array
    {
        $query = Espacio::query()
            ->with('padre.padre')
            ->where('reservable', true)
            ->where('estado', '!=', 0);

        if ($get('../../tipo_reserva') === TipoReserva::RESTAURANTE->value) {
            $query->where('tipo', TipoEspacio::MESA->value);
        }

        $principalId = $get('../../espacio_id');
        if (is_numeric($principalId)) {
            $query->whereKeyNot((int) $principalId);
        }

        return $query
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn (Espacio $espacio): array => [$espacio->id => $espacio->getNombreCompleto()])
            ->all();
    }
}
