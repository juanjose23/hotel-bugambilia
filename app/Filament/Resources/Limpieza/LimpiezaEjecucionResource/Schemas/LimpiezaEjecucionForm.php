<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Schemas;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Shared\Forms\UbicacionLimpiableSelects;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\Shared\Stock;
use App\Repository\Queries\Shared\ObtenerColaboradoresLimpieza;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class LimpiezaEjecucionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Ejecución de Limpieza')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Información General')
                            ->icon(Heroicon::InformationCircle)
                            ->columns(2)
                            ->schema([
                                UbicacionLimpiableSelects::makeTipo('limpiable_type')
                                    ->label('Tipo de Ubicación')
                                    ->required(),

                                UbicacionLimpiableSelects::makeUbicacion('limpiable_id', 'limpiable_type')
                                    ->required(),

                                Select::make('turno_id')
                                    ->label('Turno de Trabajo')
                                    ->placeholder('Seleccione el turno')
                                    ->options(fn () => Turno::where('estado', true)->pluck('nombre', 'id')->toArray())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Clock),

                                Select::make('colaborador_id')
                                    ->label('Camarista / Colaborador')
                                    ->placeholder('Seleccione el colaborador')
                                    ->options(function (Get $get) {
                                        $turnoId = $get('turno_id');
                                        if (! $turnoId) {
                                            return ObtenerColaboradoresLimpieza::opciones();
                                        }
                                        $turno = Turno::with([
                                            'lider.persona.personaNatural',
                                            'apoyo.persona.personaNatural',
                                        ])->find($turnoId);
                                        if (! $turno instanceof Turno) {
                                            return ObtenerColaboradoresLimpieza::opciones();
                                        }

                                        $colaboradores = collect();
                                        if ($turno->lider) {
                                            $colaboradores->push($turno->lider);
                                        }
                                        if ($turno->apoyo) {
                                            $colaboradores->push($turno->apoyo);
                                        }

                                        if ($colaboradores->isEmpty()) {
                                            return ObtenerColaboradoresLimpieza::opciones();
                                        }

                                        return $colaboradores
                                            ->mapWithKeys(function ($c) {
                                                $name = $c->persona
                                                    ? ObtenerNombrePersona::desde($c->persona)
                                                    : "Colaborador #{$c->id}";

                                                return [$c->id => $name];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::User),

                                Select::make('carrito_id')
                                    ->label('Carrito de Limpieza / Bodega')
                                    ->placeholder('Seleccione el carrito')
                                    ->options(fn () => Ubicacion::whereIn('tipo', ['almacen', 'bodega', 'zona'])->pluck('nombre', 'id')->toArray())
                                    ->searchable()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::ShoppingBag),

                                DatePicker::make('fecha')
                                    ->label('Fecha de Ejecución')
                                    ->required()
                                    ->prefixIcon(Heroicon::Calendar),

                                Select::make('estado')
                                    ->label('Estado')
                                    ->options(EstadoLimpieza::class)
                                    ->default(EstadoLimpieza::Pendiente)
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::ArrowPath),

                                TimePicker::make('hora_inicio')
                                    ->label('Hora de Inicio')
                                    ->prefixIcon(Heroicon::Clock),

                                TimePicker::make('hora_fin')
                                    ->label('Hora de Fin')
                                    ->prefixIcon(Heroicon::Clock),
                            ]),

                        Tab::make('Abastecimiento Recomendado')
                            ->icon(Heroicon::LightBulb)
                            ->schema([
                                TextEntry::make('abastecimiento_sugerido')
                                    ->label('Insumos Faltantes en la Habitación (Cargar antes de empezar)')
                                    ->state(function ($record) {
                                        if (! $record || $record->limpiable_type !== Habitacion::class) {
                                            return 'No hay sugerencias de abastecimiento para esta ubicación (solo disponible para habitaciones).';
                                        }
                                        $habitacion = $record->limpiable;
                                        if (! $habitacion) {
                                            return 'Ubicación no encontrada.';
                                        }

                                        $roomStocks = Stock::with(['variante.producto'])
                                            ->where('stockable_type', Habitacion::class)
                                            ->where('stockable_id', $habitacion->id)
                                            ->get();

                                        $items = [];
                                        foreach ($roomStocks as $rs) {
                                            $ideal = (float) $rs->cantidad_ideal;
                                            $actual = (float) $rs->cantidad_actual;
                                            if ($actual < $ideal && $rs->variante && $rs->variante->producto) {
                                                $nombre = $rs->variante->producto->nombre.($rs->variante->nombre_variante ? " ({$rs->variante->nombre_variante})" : '');
                                                $items[] = "- **{$nombre}**: Faltan **".($ideal - $actual)."** unidades (Ideal: {$ideal}, Actual: {$actual})";
                                            }
                                        }

                                        return empty($items)
                                            ? ' Esta habitación cuenta con el stock ideal completo. No requiere abastecimiento inicial.'
                                            : new HtmlString(implode('<br>', $items));
                                    }),
                            ]),

                        Tab::make('Checklist de Tareas')
                            ->icon(Heroicon::ClipboardDocumentCheck)
                            ->schema([
                                KeyValue::make('detalles_checklist')
                                    ->label('Checklist de Tareas')
                                    ->keyLabel('Tarea')
                                    ->valueLabel('Completada (Ej: Sí/No)')
                                    ->columnSpanFull(),

                                Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->placeholder('Describa novedades o discrepancias...')
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),

                        Tab::make('Consumos y Cambios')
                            ->icon(Heroicon::ListBullet)
                            ->schema([
                                TextEntry::make('consumos_resumen')
                                    ->label('Resumen de Insumos Consumidos del Carrito')
                                    ->state(function ($record) {
                                        if (! $record || empty($record->consumos)) {
                                            return 'No se han registrado consumos para esta ejecución de limpieza.';
                                        }

                                        $items = [];
                                        foreach ($record->consumos as $varianteId => $cantidad) {
                                            $variante = ProductoVariante::with('producto')->find($varianteId);
                                            if ($variante instanceof ProductoVariante) {
                                                $nombre = ($variante->producto ? $variante->producto->nombre : '').($variante->nombre_variante ? " ({$variante->nombre_variante})" : '');
                                                $items[] = "- **{$nombre}**: **{$cantidad}** unidades consumidas";
                                            } else {
                                                $items[] = "- **Insumo #{$varianteId}**: **{$cantidad}** unidades consumidas";
                                            }
                                        }

                                        return new HtmlString(implode('<br>', $items));
                                    }),
                            ]),
                    ]),
            ]);
    }
}
