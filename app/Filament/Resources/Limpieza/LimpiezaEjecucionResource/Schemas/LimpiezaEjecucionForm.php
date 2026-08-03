<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Schemas;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Shared\Forms\UbicacionLimpiableSelects;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
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
                                    ->getOptionLabelUsing(function ($value): string {
                                        if (! is_numeric($value)) {
                                            return (string) $value;
                                        }

                                        $colaborador = Colaborador::query()
                                            ->with(['persona.personaNatural', 'persona.personaJuridica'])
                                            ->find((int) $value);

                                        if (! $colaborador) {
                                            return "Colaborador #{$value}";
                                        }

                                        return $colaborador->persona
                                            ? ObtenerNombrePersona::desde($colaborador->persona)
                                            : "Colaborador #{$colaborador->id}";
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
                                    ->label(function ($record) {
                                        if (! $record || ! $record->limpiable) {
                                            return 'Insumos Recomendados';
                                        }

                                        $nombre = $record->limpiable->nombre ?? 'esta ubicación';

                                        return "Insumos Recomendados — {$nombre}";
                                    })
                                    ->state(function ($record) {
                                        if (! $record || ! $record->limpiable) {
                                            return new HtmlString(
                                                '<div class="text-gray-500 dark:text-gray-400 text-sm">'
                                                .'Seleccione el tipo de ubicación y el objeto para ver las recomendaciones de abastecimiento.'
                                                .'</div>'
                                            );
                                        }

                                        $limpiable = $record->limpiable;
                                        $modelClass = $record->limpiable_type;
                                        $nombre = $limpiable->nombre ?? 'Sin nombre';

                                        // Determinar el stockable_type según el tipo de limpiable
                                        $isHabitacion = $modelClass === Habitacion::class;
                                        $isEspacio = $modelClass === Espacio::class;

                                        $stockClass = match (true) {
                                            $isHabitacion => Habitacion::class,
                                            $isEspacio => Espacio::class,
                                            default => null,
                                        };

                                        if (! $stockClass) {
                                            $svg = self::svgIcon('heroicon-o-exclamation-triangle', 'text-gray-400');

                                            return new HtmlString(
                                                '<div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">'
                                                .'<div class="flex items-center gap-2 mb-2">'
                                                .$svg
                                                .'<span class="font-semibold text-gray-700 dark:text-gray-300">Sin plantilla de stock</span>'
                                                .'</div>'
                                                .'<span class="text-sm text-gray-500 dark:text-gray-400">'
                                                .'El tipo <strong>'.class_basename($modelClass).'</strong> no tiene stock configurado.'
                                                .'</span></div>'
                                            );
                                        }

                                        $roomStocks = Stock::with(['variante.producto'])
                                            ->where('stockable_type', $stockClass)
                                            ->where('stockable_id', $limpiable->getKey())
                                            ->get();

                                        if ($roomStocks->isEmpty()) {
                                            $svg = self::svgIcon('heroicon-o-clipboard-document-list', 'text-amber-500');

                                            return new HtmlString(
                                                '<div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800">'
                                                .'<div class="flex items-center gap-2 mb-1">'
                                                .$svg
                                                .'<span class="font-semibold text-amber-700 dark:text-amber-300">Sin stock configurado</span>'
                                                .'</div>'
                                                .'<span class="text-sm text-amber-600 dark:text-amber-400">'
                                                ."<strong>{$nombre}</strong> no tiene productos con stock ideal definido."
                                                .'</span></div>'
                                            );
                                        }

                                        $items = [];
                                        $completo = true;
                                        foreach ($roomStocks as $rs) {
                                            $ideal = (float) $rs->cantidad_ideal;
                                            $actual = (float) $rs->cantidad_actual;
                                            if ($rs->variante && $rs->variante->producto) {
                                                $productoNombre = $rs->variante->producto->nombre;
                                                $varianteNombre = $rs->variante->nombre_variante ? " ({$rs->variante->nombre_variante})" : '';
                                                $nombreItem = "{$productoNombre}{$varianteNombre}";

                                                if ($actual < $ideal) {
                                                    $completo = false;
                                                    $faltante = $ideal - $actual;
                                                    $items[] = "<li class='flex justify-between items-center py-1 px-2 rounded-lg bg-red-50 dark:bg-red-950/20'>"
                                                        ."<span class='font-medium text-gray-900 dark:text-gray-100 text-sm'>{$nombreItem}</span>"
                                                        ."<span class='text-xs font-bold text-red-600 dark:text-red-400'>"
                                                        ."Falta {$faltante} de {$ideal}"
                                                        .'</span></li>';
                                                } else {
                                                    $items[] = "<li class='flex justify-between items-center py-1 px-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/20'>"
                                                        ."<span class='text-sm text-gray-600 dark:text-gray-400'>{$nombreItem}</span>"
                                                        ."<span class='text-xs font-bold text-emerald-600 dark:text-emerald-400'>"
                                                        ."{$actual} / {$ideal} Completo"
                                                        .'</span></li>';
                                                }
                                            }
                                        }

                                        $headerColor = $completo ? 'emerald' : 'red';
                                        $iconName = $completo ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle';
                                        $iconColorClass = 'text-'.$headerColor.'-500';
                                        $svg = self::svgIcon($iconName, $iconColorClass);
                                        $headerText = $completo
                                            ? 'Stock completo - no requiere abastecimiento'
                                            : 'Requiere abastecimiento antes de iniciar';

                                        $html = "<div class='p-4 rounded-xl bg-{$headerColor}-50 dark:bg-{$headerColor}-950/20 border border-{$headerColor}-200 dark:border-{$headerColor}-800'>"
                                            ."<div class='flex items-center gap-2 mb-3'>"
                                            .$svg
                                            ."<span class='font-semibold text-{$headerColor}-700 dark:text-{$headerColor}-300 text-sm'>{$headerText}</span>"
                                            .'</div>'
                                            ."<p class='text-xs text-gray-500 dark:text-gray-400 mb-2'>"
                                            ."<strong>{$nombre}</strong> &middot; ".($isHabitacion ? 'Habitacion' : 'Espacio')
                                            .'</p>'
                                            ."<ul class='space-y-1'>"
                                            .implode('', $items)
                                            .'</ul></div>';

                                        return new HtmlString($html);
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

    private static function svgIcon(string $name, string $class = ''): string
    {
        try {
            $svg = svg($name, 'w-5 h-5 '.$class)->toHtml();
        } catch (\Throwable) {
            $svg = '';
        }

        return $svg;
    }
}
