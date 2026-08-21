<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Forms\Limpieza\StockUbicacionSelect;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerUbicacionesInventarioLavanderia;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

final class ConsumoJornadaLavanderiaForm
{
    /**
     * @return list<Component>
     */
    public static function schema(ObtenerUbicacionesInventarioLavanderia $ubicacionesInventarioLavanderia): array
    {
        return [
            Section::make('Cierre de Jornada y Consumo de Insumos de Lavandería')
                ->description('Registre los insumos químicos utilizados, kilos de ropa procesados y las mermas del turno de trabajo.')
                ->columns(2)
                ->schema([
                    DatePicker::make('fecha')
                        ->label('Fecha de la Jornada')
                        ->default(now()->toDateString())
                        ->required()
                        ->native(false)
                        ->prefixIcon(Heroicon::CalendarDays),

                    Select::make('turno_id')
                        ->label('Turno Asignado')
                        ->options(function (): array {
                            /** @var array<int, string> */
                            return Turno::query()
                                ->where('estado', EstadoGeneral::Activo)
                                ->orderBy('es_lavanderia', 'desc')
                                ->orderBy('hora_inicio')
                                ->get()
                                ->mapWithKeys(function (Turno $t): array {
                                    $nombreLider = $t->lider?->persona?->nombre_completo;
                                    $label = $t->nombre;
                                    if ($t->hora_inicio && $t->hora_fin) {
                                        $label .= " ({$t->hora_inicio} - {$t->hora_fin})";
                                    }
                                    if ($nombreLider) {
                                        $label .= " · Líder: {$nombreLider}";
                                    }

                                    return [(int) $t->id => $label];
                                })
                                ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->native(false)
                        ->placeholder('Seleccione turno oficial')
                        ->prefixIcon(Heroicon::Clock)
                        ->afterStateUpdated(function (?int $state, Set $set): void {
                            if ($state !== null && $state > 0) {
                                $turno = Turno::query()->find($state);
                                if ($turno instanceof Turno) {
                                    $persona = $turno->lider?->persona;
                                    $nombreLider = $persona?->nombre_completo;
                                    if ($nombreLider) {
                                        $set('operador_nombre', $nombreLider);
                                    }
                                }
                            }
                        }),

                    TextInput::make('operador_nombre')
                        ->label('Jefe de Turno / Operador Responsable')
                        ->placeholder('Ej. Juan Pérez')
                        ->maxLength(100)
                        ->prefixIcon(Heroicon::User),

                    TextInput::make('kilos_lavados')
                        ->label('Kilos de Ropa Procesados (Opcional)')
                        ->numeric()
                        ->minValue(0.1)
                        ->placeholder('Ej. 150 kg')
                        ->prefixIcon(Heroicon::Scale),

                    Repeater::make('insumos')
                        ->label('Insumos Químicos Consumidos en el Turno (Obligatorio)')
                        ->columnSpanFull()
                        ->columns([
                            'default' => 1,
                            'md' => 12,
                        ])
                        ->schema([
                            StockUbicacionSelect::make(
                                column: 'stock_id',
                                label: 'Insumo Químico / Consumible',
                                ubicacionId: fn (Get $get): array => $ubicacionesInventarioLavanderia->execute(),
                            )->columnSpan([
                                'default' => 12,
                                'md' => 6,
                            ]),

                            TextInput::make('cantidad')
                                ->label('Cantidad Consumida')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) && (float) $get('max_qty') > 0 ? (float) $get('max_qty') : 999999.0)
                                ->placeholder('Ej. 3.5')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 3,
                                ]),

                            Hidden::make('max_qty')
                                ->default(0),

                            TextInput::make('notas')
                                ->label('Detalle / Notas')
                                ->placeholder('Ej. Doble dosificación...')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 3,
                                ]),
                        ])
                        ->addActionLabel('Agregar otro insumo químico')
                        ->defaultItems(1),

                    Toggle::make('sin_mermas')
                        ->label('Declarar este turno SIN mermas ni piezas dañadas')
                        ->default(true)
                        ->live()
                        ->columnSpanFull(),

                    Repeater::make('mermas')
                        ->label('Mermas / Piezas de Ropa Blanca Dañadas en este Turno')
                        ->visible(fn (Get $get): bool => ! (bool) $get('sin_mermas'))
                        ->columnSpanFull()
                        ->columns([
                            'default' => 1,
                            'md' => 12,
                        ])
                        ->schema([
                            StockUbicacionSelect::make(
                                column: 'stock_id',
                                label: 'Prenda / Lencería a dar de baja',
                                ubicacionId: fn (Get $get): array => $ubicacionesInventarioLavanderia->execute(),
                            )->columnSpan([
                                'default' => 12,
                                'md' => 6,
                            ]),

                            TextInput::make('cantidad')
                                ->label('Piezas Dañadas')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) && (float) $get('max_qty') > 0 ? (float) $get('max_qty') : 999999.0)
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 3,
                                ]),

                            Hidden::make('max_qty')
                                ->default(0),

                            TextInput::make('notas')
                                ->label('Motivo de Baja')
                                ->placeholder('Ej. Quemadura, rasgadura irreparable...')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 3,
                                ]),
                        ])
                        ->addActionLabel('Agregar pieza con merma')
                        ->defaultItems(1),

                    TextInput::make('observaciones')
                        ->label('Observaciones Generales del Turno / Relevo')
                        ->placeholder('Ej. Turno sin novedades, centrifugadora #2 revisada...')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
