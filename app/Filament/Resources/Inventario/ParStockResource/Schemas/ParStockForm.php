<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ParStockResource\Schemas;

use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ParStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Configuración de PAR Stock')
                    ->description('Defina los niveles mínimos y óptimos para reponer automáticamente el inventario en bodegas físicas.')
                    ->columns(2)
                    ->schema([
                        Select::make('producto_id')
                            ->label('Producto')
                            ->relationship('producto', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('producto_variante_id', null))
                            ->prefixIcon(Heroicon::ArchiveBox),

                        Select::make('producto_variante_id')
                            ->label('Variante')
                            ->placeholder('Primero seleccione un producto (opcional)')
                            ->options(fn ($get): array => self::getVariantesOptions($get('producto_id')))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon(Heroicon::AdjustmentsHorizontal),

                        Select::make('ubicacion_id')
                            ->label('Bodega de Destino')
                            ->options(fn () => Ubicacion::where('tipo', 'almacen')->where('estado', 1)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->prefixIcon(Heroicon::MapPin),

                        TextInput::make('stock_minimo')
                            ->label('Stock Mínimo (Punto de Re-orden)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->prefixIcon(Heroicon::ArrowDownCircle),

                        TextInput::make('stock_objetivo')
                            ->label('Stock Objetivo (Límite Máximo)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->prefixIcon(Heroicon::ArrowUpCircle),
                    ]),
            ]);
    }

    /** @return array<int, string> */
    public static function getVariantesOptions(?int $productoId): array
    {
        if ($productoId === null) {
            return [];
        }

        return ProductoVariante::where('producto_id', $productoId)
            ->get()
            ->mapWithKeys(function (ProductoVariante $v) {
                $info = $v->codigo;

                if ($v->atributos) {
                    $attrs = collect($v->atributos)
                        ->map(fn ($val, $key) => "{$key}: {$val}")
                        ->implode(', ');
                    $info .= " | {$attrs}";
                }

                if ($v->unidadMedida) {
                    $info .= " ({$v->unidadMedida->nombre})";
                }

                return [$v->id => $info];
            })
            ->toArray();
    }
}
