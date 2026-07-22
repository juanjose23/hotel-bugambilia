<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\MovimientoStock\Tables;

use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Queries\Inventario\Movimientos\ObtenerTiposMovimientoInventario;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MovimientoStockTable
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly ObtenerTiposMovimientoInventario $obtenerTiposMovimientoInventario,
    ) {}

    public static function configure(Table $table): Table
    {
        return static::make()->doConfigure($table);
    }

    private function doConfigure(Table $table): Table
    {
        return $table
            ->columns([
                FechaStandardColumn::make('created_at', 'Fecha')
                    ->sortable(),
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
                    ->options(fn () => $this->obtenerTiposMovimientoInventario->execute()),
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),
            ]);
    }
}
