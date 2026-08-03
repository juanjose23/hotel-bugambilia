<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Restaurante;

use App\Enums\Reservas\TipoReserva;
use App\Filament\Shared\Forms\MesaSelect;
use App\Repository\Queries\Reservas\CalcularResumenRestauranteQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class EsquemaReservaRestaurante
{
    /**
     * @return array<int, Section>
     */
    public static function make(): array
    {
        $pedidoQuery = app(ObtenerDatosPedidoFormQuery::class);

        return [
            Section::make('Horario y Capacidad del Restaurante')
                ->columnSpanFull()
                ->icon(Heroicon::Clock)
                ->columns(4)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value)
                ->schema([
                    DatePicker::make('fecha_check_in')
                        ->label('Fecha de Reservación')
                        ->prefixIcon(Heroicon::CalendarDays)
                        ->suffixIcon('heroicon-m-chevron-down')
                        ->native(false)
                        ->closeOnDateSelection()
                        ->firstDayOfWeek(1)
                        ->displayFormat('d/m/Y')
                        ->minDate(now('America/Managua')->startOfDay())
                        ->required()
                        ->validationMessages([
                            'required' => 'Seleccione la fecha de la reservación.',
                            'after_or_equal' => 'La fecha de la reservación no puede estar en el pasado.',
                        ])
                        ->default(fn () => now('America/Managua'))
                        ->disabledOn('edit')
                        ->columnSpan(1),

                    TimePicker::make('hora_reserva')
                        ->label('Hora de Reservación (Nicaragua)')
                        ->prefixIcon(Heroicon::Clock)
                        ->suffixIcon('heroicon-m-chevron-down')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->default(fn (): string => now('America/Managua')->format('H:i'))
                        ->required()
                        ->validationMessages([
                            'required' => 'Seleccione la hora de llegada del cliente.',
                        ])
                        ->disabledOn('edit')
                        ->columnSpan(1),

                    Select::make('duracion_horas')
                        ->label('Duración Estimada')
                        ->options([
                            1 => '1 Hora',
                            2 => '2 Horas',
                            3 => '3 Horas',
                            4 => '4 Horas',
                            5 => '5 Horas o evento',
                        ])
                        ->default(2)
                        ->required()
                        ->validationMessages([
                            'required' => 'Seleccione cuántas horas se reservará el restaurante.',
                        ])
                        ->live()
                        ->native(false)
                        ->columnSpan(1),

                    TextInput::make('adultos')
                        ->label('Total de Comensales')
                        ->numeric()
                        ->default(2)
                        ->minValue(1)
                        ->required()
                        ->validationMessages([
                            'required' => 'Indique la cantidad total de comensales.',
                            'numeric' => 'La cantidad de comensales debe ser un número.',
                            'min' => 'La reserva debe incluir al menos un comensal.',
                        ])
                        ->live()
                        ->disabledOn('edit')
                        ->columnSpan(1),
                ]),

            Section::make('Mesa y Ubicación del Restaurante')
                ->columnSpanFull()
                ->icon(Heroicon::UserGroup)
                ->columns(2)
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value)
                ->schema([
                    MesaSelect::make(column: 'espacio_id', soloReservables: true)
                        ->required(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value)
                        ->validationMessages([
                            'required' => 'Seleccione la mesa principal de la reservación.',
                        ])
                        ->live()
                        ->columnSpan(1),

                    Toggle::make('cobrar_tarifa_mesa')
                        ->label('Incluir tarifa / alquiler de mesa')
                        ->helperText('Actívelo para alquiler de espacio o mesa (VIP, evento privado). Desactívelo para consumo regular en restaurante.')
                        ->default(false)
                        ->live()
                        ->columnSpan(1),
                ]),

            Section::make('Pre-orden de Degustación (Platillos Opcionales para Cocina)')
                ->columnSpanFull()
                ->icon(Heroicon::ShoppingBag)
                ->description('Seleccione los platillos que el cliente solicita tener preparados inmediatamente a su llegada.')
                ->visible(fn ($get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value)
                ->schema([
                    Repeater::make('items_preorden')
                        ->label('Platillos Pre-ordenados')
                        ->schema([
                            Select::make('plato_id')
                                ->label('Platillo')
                                ->options(fn (): array => $pedidoQuery->platosActivosAgrupadosPorCategoria())
                                ->required()
                                ->validationMessages([
                                    'required' => 'Seleccione el platillo de la preorden.',
                                ])
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) use ($pedidoQuery): void {
                                    if (! is_numeric($state)) {
                                        return;
                                    }

                                    $set(
                                        'precio_unitario',
                                        $pedidoQuery->precioActualDePlato((int) $state) ?? 0.0,
                                    );
                                })
                                ->columnSpan(2),

                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->validationMessages([
                                    'required' => 'Indique cuántas unidades del platillo desea ordenar.',
                                    'numeric' => 'La cantidad del platillo debe ser numérica.',
                                    'min' => 'Debe ordenar al menos una unidad del platillo.',
                                ])
                                ->live()
                                ->columnSpan(1),

                            TextInput::make('precio_unitario')
                                ->label('Precio Unitario')
                                ->numeric()
                                ->prefix('C$')
                                ->readOnly()
                                ->columnSpan(1),

                            TextInput::make('observaciones')
                                ->label('Observaciones / Notas de Cocción')
                                ->placeholder('Ej: Término medio, sin cebolla, salsa aparte')
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->defaultItems(0)
                        ->live()
                        ->addActionLabel('Añadir Platillo a la Pre-orden'),
                ]),

            Section::make('Cálculo de la reserva del restaurante')
                ->columnSpanFull()
                ->icon(Heroicon::Calculator)
                ->description('Incluye las horas reservadas, las mesas seleccionadas y todos los platillos de la preorden.')
                ->visible(fn (Get $get): bool => $get('tipo_reserva') === TipoReserva::RESTAURANTE->value)
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('resumen_horas_restaurante')
                        ->label('Tiempo reservado')
                        ->state(fn (Get $get): string => self::resumen($get)['horas'].' hora(s)'),
                    TextEntry::make('resumen_mesas_restaurante')
                        ->label('Mesas necesarias')
                        ->state(function (Get $get): string {
                            $resumen = self::resumen($get);

                            return "{$resumen['mesas_requeridas']} requerida(s) · {$resumen['mesas_seleccionadas']} seleccionada(s)";
                        })
                        ->helperText('Si faltan mesas, agréguelas en “Espacios adicionales”.'),
                    TextEntry::make('resumen_union_mesas_restaurante')
                        ->label('Mesas que se unirán')
                        ->state(function (Get $get): string {
                            $resumen = self::resumen($get);
                            $seleccionadas = $resumen['mesas_seleccionadas_nombres'];
                            $sugeridas = array_column($resumen['mesas_sugeridas'], 'nombre');

                            if ($seleccionadas === []) {
                                return 'Seleccione primero la mesa principal.';
                            }

                            $adultosVal = $get('adultos');
                            $adultosCant = is_numeric($adultosVal) ? (int) $adultosVal : 1;

                            if ($resumen['capacidad_total'] >= $adultosCant) {
                                return implode(' + ', $seleccionadas);
                            }

                            return $sugeridas === []
                                ? implode(' + ', $seleccionadas).' · No hay mesas suficientes disponibles'
                                : implode(' + ', [...$seleccionadas, ...$sugeridas]).' (propuesta)';
                        })
                        ->helperText('Las mesas propuestas deben agregarse en Espacios adicionales para confirmar la unión.'),
                    Actions::make([
                        Action::make('agregar_sugerencia_mesas')
                            ->label('Agregar sugerencia')
                            ->icon(Heroicon::PlusCircle)
                            ->color('gray')
                            ->button()
                            ->visible(fn (Get $schemaGet): bool => self::resumen($schemaGet)['mesas_sugeridas'] !== [])
                            ->action(function (Get $schemaGet, Set $schemaSet): void {
                                $resumen = self::resumen($schemaGet);
                                $actuales = is_array($schemaGet('espacios_adicionales'))
                                    ? array_values($schemaGet('espacios_adicionales'))
                                    : [];
                                $idsActuales = collect($actuales)
                                    ->filter(fn (mixed $item): bool => is_array($item) && is_numeric($item['espacio_id'] ?? null))
                                    ->map(fn (array $item): int => (int) $item['espacio_id'])
                                    ->all();

                                foreach ($resumen['mesas_sugeridas'] as $mesa) {
                                    if (in_array($mesa['id'], $idsActuales, true)) {
                                        continue;
                                    }

                                    $actuales[] = [
                                        'espacio_id' => $mesa['id'],
                                        'cantidad' => 1,
                                    ];
                                }

                                $schemaSet('espacios_adicionales', $actuales);
                            }),
                    ])
                        ->columnSpanFull(),
                    TextEntry::make('resumen_capacidad_restaurante')
                        ->label('Capacidad seleccionada')
                        ->state(fn (Get $get): string => self::resumen($get)['capacidad_total'].' persona(s)'),
                    TextEntry::make('resumen_costo_mesas_restaurante')
                        ->label('Costo de mesa(s)')
                        ->state(fn (Get $get): string => 'C$ '.number_format(self::resumen($get)['costo_mesas'], 2)),
                    TextEntry::make('resumen_costo_preorden_restaurante')
                        ->label('Total preorden')
                        ->state(fn (Get $get): string => 'C$ '.number_format(self::resumen($get)['costo_preorden'], 2)),
                    TextEntry::make('resumen_subtotal_restaurante')
                        ->label('Subtotal de la reserva')
                        ->state(fn (Get $get): string => 'C$ '.number_format(self::resumen($get)['subtotal'], 2)),
                    TextEntry::make('resumen_abono_restaurante')
                        ->label('Abono exacto del 50 %')
                        ->state(fn (Get $get): string => 'C$ '.number_format(self::resumen($get)['abono_50'], 2)),
                ]),
        ];
    }

    /**
     * @return array{horas: int, mesas_requeridas: int, mesas_seleccionadas: int, capacidad_total: int, costo_mesas: float, costo_preorden: float, subtotal: float, abono_50: float, mesas_seleccionadas_nombres: array<int, string>, mesas_sugeridas: array<int, array{id: int, nombre: string, capacidad: int}>}
     */
    private static function resumen(Get $get): array
    {
        return app(CalcularResumenRestauranteQuery::class)->ejecutar(
            mesaPrincipalId: is_numeric($get('espacio_id')) ? (int) $get('espacio_id') : null,
            comensales: is_numeric($get('adultos')) ? (int) $get('adultos') : 1,
            horas: is_numeric($get('duracion_horas')) ? (int) $get('duracion_horas') : 1,
            espaciosAdicionales: is_array($get('espacios_adicionales')) ? $get('espacios_adicionales') : [],
            itemsPreorden: is_array($get('items_preorden')) ? $get('items_preorden') : [],
        );
    }
}
