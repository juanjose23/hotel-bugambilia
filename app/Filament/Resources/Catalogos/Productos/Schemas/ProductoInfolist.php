<?php

namespace App\Filament\Resources\Catalogos\Productos\Schemas;

use App\Enums\EstadoCatalogo;
use App\Enums\TipoProducto;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->description('Datos maestros del producto')
                    ->icon(Heroicon::InformationCircle)
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre del producto')
                            ->icon(Heroicon::Tag)
                            ->weight('bold')
                            ->size('lg')
                            ->copyable()
                            ->columnSpan(2),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->icon(Heroicon::CheckCircle)
                            ->badge()
                            ->color(fn ($state): string => EstadoCatalogo::colorFor($state))
                            ->formatStateUsing(callback: fn ($state): string => EstadoCatalogo::labelFor($state))
                            ->columnSpan(1),
                        TextEntry::make('categoria.nombre')
                            ->label('Categoría')
                            ->icon(Heroicon::ArchiveBox)
                            ->placeholder('Sin categoría')
                            ->columnSpan(1),
                        TextEntry::make('marca.nombre')
                            ->label('Marca')
                            ->icon(Heroicon::ShoppingCart)
                            ->placeholder('Sin marca')
                            ->columnSpan(1),
                        TextEntry::make('unidadMedida.nombre')
                            ->label('Unidad de medida base')
                            ->icon(Heroicon::Scale)
                            ->placeholder('Sin asignar')
                            ->columnSpan(1),
                        TextEntry::make('tipo')
                            ->label('Tipo')
                            ->icon(Heroicon::Check)
                            ->badge()
                            ->color(fn ($state): string => TipoProducto::colorFor($state))
                            ->formatStateUsing(fn ($state): string => TipoProducto::labelFor($state)),
                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->icon(Heroicon::Document)
                            ->placeholder('Sin descripción')
                            ->columnSpanFull()
                            ->markdown()
                            ->limit(200),

                    ]),

                Section::make('Auditoría')
                    ->description('Fechas de registro en el sistema')
                    ->collapsed()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
