<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ActivoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Detalle del Activo')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Información General')
                        ->icon(Heroicon::InformationCircle)
                        ->columns(3)
                        ->schema([
                            TextEntry::make('codigo_inventario')
                                ->label('Código de Inventario')
                                ->badge()
                                ->color('primary')
                                ->copyable()
                                ->icon(Heroicon::Hashtag)
                                ->weight(FontWeight::Bold),

                            TextEntry::make('nombre_descriptivo')
                                ->label('Nombre / Descripción')
                                ->icon(Heroicon::Tag)
                                ->weight(FontWeight::Bold),

                            TextEntry::make('numero_serie')
                                ->label('Número de Serie')
                                ->icon(Heroicon::QrCode)
                                ->placeholder('No registrado'),

                            TextEntry::make('producto.nombre')
                                ->label('Producto')
                                ->icon(Heroicon::Cube),

                            TextEntry::make('variante.nombre_variante')
                                ->label('Variante')
                                ->placeholder('Sin variante')
                                ->icon(Heroicon::RectangleStack),

                            TextEntry::make('producto.categoria.nombre')
                                ->label('Categoría')
                                ->icon(Heroicon::Folder)
                                ->placeholder('Sin categoría'),

                            TextEntry::make('producto.marca.nombre')
                                ->label('Marca')
                                ->icon(Heroicon::Bookmark)
                                ->placeholder('Sin marca'),

                            TextEntry::make('estado')
                                ->label('Estado Operativo')
                                ->badge(),

                            TextEntry::make('fecha_adquisicion')
                                ->label('Fecha de Adquisición')
                                ->date('d/m/Y')
                                ->icon(Heroicon::Calendar),

                            TextEntry::make('costo_adquisicion')
                                ->label('Costo de Adquisición')
                                ->money(fn ($record) => $record->moneda->codigo ?? 'USD')
                                ->icon(Heroicon::CurrencyDollar)
                                ->placeholder('No registrado'),

                            TextEntry::make('moneda.nombre')
                                ->label('Moneda')
                                ->icon(Heroicon::Banknotes)
                                ->placeholder('No especificada'),

                            TextEntry::make('proveedor.codigo')
                                ->label('Proveedor')
                                ->icon(Heroicon::BuildingOffice2)
                                ->formatStateUsing(fn ($state, $record) => $record->proveedor
                                    ? $record->proveedor->codigo.' - '.(
                                        ($record->proveedor->persona && $record->proveedor->persona->personaJuridica ? $record->proveedor->persona->personaJuridica->razon_social : null)
                                        ?? ($record->proveedor->persona ? $record->proveedor->persona->primer_nombre.' '.($record->proveedor->persona->personaNatural ? $record->proveedor->persona->personaNatural->primer_apellido : '') : '')
                                    )
                                    : null)
                                ->placeholder('No registrado'),

                            TextEntry::make('vida_util_meses')
                                ->label('Vida Útil')
                                ->suffix(' meses')
                                ->icon(Heroicon::Clock)
                                ->placeholder('No especificada'),

                            TextEntry::make('fecha_garantia_fin')
                                ->label('Fin de Garantía')
                                ->date('d/m/Y')
                                ->icon(Heroicon::ShieldCheck)
                                ->placeholder('Sin garantía registrada'),

                            TextEntry::make('valor_libros')
                                ->label('Valor Neto en Libros')
                                ->state(function ($record) {
                                    if (! $record->costo_adquisicion || ! $record->fecha_adquisicion || ! $record->vida_util_meses) {
                                        return null;
                                    }
                                    $costo = (float) $record->costo_adquisicion;
                                    $vidaUtil = (int) $record->vida_util_meses;
                                    $meses = now()->diffInMonths($record->fecha_adquisicion);
                                    if ($meses >= $vidaUtil) {
                                        return 0.00;
                                    }
                                    $depAcumulada = ($costo / $vidaUtil) * $meses;

                                    return max(0.00, $costo - $depAcumulada);
                                })
                                ->money(fn ($record) => $record->moneda->codigo ?? 'USD')
                                ->icon(Heroicon::Banknotes)
                                ->color('success')
                                ->weight(FontWeight::Bold),

                            TextEntry::make('depreciacion_acumulada')
                                ->label('Depreciación Acumulada')
                                ->state(function ($record) {
                                    if (! $record->costo_adquisicion || ! $record->fecha_adquisicion || ! $record->vida_util_meses) {
                                        return null;
                                    }
                                    $costo = (float) $record->costo_adquisicion;
                                    $vidaUtil = (int) $record->vida_util_meses;
                                    $meses = now()->diffInMonths($record->fecha_adquisicion);
                                    if ($meses >= $vidaUtil) {
                                        return $costo;
                                    }

                                    return ($costo / $vidaUtil) * $meses;
                                })
                                ->money(fn ($record) => $record->moneda->codigo ?? 'USD')
                                ->icon(Heroicon::ArrowTrendingDown)
                                ->placeholder('0.00'),
                        ]),

                    Tab::make('Mantenimiento')
                        ->icon(Heroicon::Wrench)
                        ->schema([
                            RepeatableEntry::make('mantenimientos')
                                ->hiddenLabel()
                                ->contained(false)
                                ->schema([
                                    TextEntry::make('fecha_inicio')
                                        ->label('Fecha')
                                        ->date('d/m/Y')
                                        ->badge()
                                        ->color('warning')
                                        ->icon(Heroicon::Calendar),
                                    TextEntry::make('tipo')
                                        ->label('Tipo')
                                        ->badge(),
                                    TextEntry::make('descripcion')
                                        ->label('Descripción')
                                        ->limit(60),
                                    TextEntry::make('costo')
                                        ->label('Costo')
                                        ->money('USD')
                                        ->placeholder('—'),

                                    TextEntry::make('estado')
                                        ->label('Estado')
                                        ->badge(),
                                ])
                                ->placeholder('No hay mantenimientos registrados.'),
                        ]),

                    Tab::make('Historial de Ubicaciones')
                        ->icon(Heroicon::MapPin)
                        ->schema([
                            RepeatableEntry::make('asignaciones')
                                ->hiddenLabel()
                                ->contained(false)
                                ->schema([
                                    TextEntry::make('fecha_inicio')
                                        ->label('Inicio')
                                        ->date('d/m/Y')
                                        ->badge()
                                        ->color('success')
                                        ->icon(Heroicon::Calendar),
                                    TextEntry::make('fecha_fin')
                                        ->label('Fin')
                                        ->date('d/m/Y')
                                        ->badge()
                                        ->color('danger')
                                        ->icon(Heroicon::Calendar)
                                        ->placeholder('Vigente'),
                                    TextEntry::make('tipo_destino')
                                        ->label('Tipo de Destino')
                                        ->state(fn ($record) => $record->tipoDestinoLabel())
                                        ->badge()
                                        ->color(fn ($record) => $record->tipoDestinoColor()),
                                    TextEntry::make('destino')
                                        ->label('Destino')
                                        ->state(fn ($record) => $record->destinoLabel())
                                        ->weight(FontWeight::Bold),
                                    TextEntry::make('motivo')
                                        ->label('Motivo / Justificación'),
                                    TextEntry::make('asignadoPor.name')
                                        ->label('Asignado Por')
                                        ->placeholder('Sistema'),
                                ])
                                ->placeholder('No hay historial de traslados registrado.'),
                        ]),

                    Tab::make('Notas')
                        ->icon(Heroicon::DocumentText)
                        ->schema([
                            TextEntry::make('notas')
                                ->hiddenLabel()
                                ->prose()
                                ->placeholder('Sin notas registradas.'),
                        ]),
                ]),
        ]);
    }
}
