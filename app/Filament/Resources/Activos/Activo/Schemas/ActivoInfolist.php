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
