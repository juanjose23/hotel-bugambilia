<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LoteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Lote')
                    ->icon(Heroicon::Cube)
                    ->schema([
                        TextEntry::make('codigo_lote')
                            ->label('Código de Lote'),
                        TextEntry::make('producto.nombre')
                            ->label('Producto'),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge(),
                        TextEntry::make('producto_variante_id')
                            ->label('Variante'),
                        TextEntry::make('cantidad_disponible')
                            ->label('Cantidad Disponible')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('cantidad_inicial')
                            ->label('Cantidad Inicial')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('ubicacion.nombre')
                            ->label('Ubicación'),
                        TextEntry::make('fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->date('d/m/Y'),
                        TextEntry::make('lote_proveedor')
                            ->label('Lote del Proveedor'),
                        TextEntry::make('fecha_recepcion')
                            ->label('Fecha de Recepción')
                            ->date('d/m/Y'),
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Historial de Movimientos')
                    ->icon(Heroicon::Clock)
                    ->schema([
                        RepeatableEntry::make('movimientos')
                            ->label('')
                            ->schema([
                                TextEntry::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                                TextEntry::make('tipo')->label('Tipo')->badge(),
                                TextEntry::make('cantidad')->label('Cantidad'),
                                TextEntry::make('ubicacionOrigen.nombre')->label('Origen'),
                                TextEntry::make('ubicacionDestino.nombre')->label('Destino'),
                                TextEntry::make('referencia')->label('Referencia'),
                            ])
                            ->columns(6),
                    ]),
            ]);
    }
}
