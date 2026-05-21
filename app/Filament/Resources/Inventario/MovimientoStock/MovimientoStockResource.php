<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\MovimientoStock;

use App\Filament\Resources\Inventario\MovimientoStock\Pages\ListMovimientoStock;
use App\Filament\Resources\Inventario\MovimientoStock\Widgets\MermasPorCategoriaChart;
use App\Filament\Resources\Inventario\MovimientoStock\Widgets\RotacionInventarioChart;
use App\Models\Catalogos\Catalogo;
use App\Models\Inventario\MovimientoStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MovimientoStockResource extends Resource
{
    protected static ?string $model = MovimientoStock::class;

    protected static ?string $slug = 'inventario/movimientos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Movimientos de Stock';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('tipoCatalogo.nombre')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (MovimientoStock $record) => match ($record->tipo) {
                        'MOV_ENTRADA' => 'success',
                        'MOV_SALIDA' => 'danger',
                        'MOV_TRANSFERENCIA' => 'info',
                        'MOV_AJUSTE' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('producto.nombre')->label('Producto')->sortable()->searchable(),
                TextColumn::make('cantidad')->label('Cantidad')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('lote.codigo_lote')->label('Lote'),
                TextColumn::make('ubicacionOrigen.nombre')->label('Origen'),
                TextColumn::make('ubicacionDestino.nombre')->label('Destino'),
                TextColumn::make('referencia')->label('Referencia'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(function () {
                        return Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'TIPO_MOVIMIENTO_INV'))
                            ->pluck('nombre', 'codigo')
                            ->toArray();
                    }),
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            RotacionInventarioChart::class,
            MermasPorCategoriaChart::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientoStock::route('/'),
        ];
    }
}
