<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DevolucionCompraInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle de Devolución')
                    ->description('Información de la salida física de mercancía al proveedor')
                    ->icon(Heroicon::Trash)
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Devolución')
                            ->badge()
                            ->color('primary')
                            ->icon(Heroicon::QrCode),

                        TextEntry::make('ordenCompra.codigo')
                            ->label('Orden de Compra')
                            ->icon(Heroicon::ShoppingCart),

                        TextEntry::make('recepcionCompra.codigo')
                            ->label('Recepción')
                            ->placeholder('N/A')
                            ->icon(Heroicon::ArchiveBox),

                        TextEntry::make('fecha_devolucion')
                            ->label('Fecha')
                            ->date('d/m/Y')
                            ->icon(Heroicon::Calendar),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge(),

                        TextEntry::make('creador.name')
                            ->label('Responsable')
                            ->icon(Heroicon::User),

                        TextEntry::make('documento_externo')
                            ->label('Nota de Crédito / Guía')
                            ->icon(Heroicon::Hashtag)
                            ->placeholder('—'),

                        TextEntry::make('motivo')
                            ->label('Motivo / Justificación')
                            ->columnSpanFull()
                            ->placeholder('Sin observaciones'),
                    ]),

                Section::make('Ítems Devueltos')
                    ->icon(Heroicon::QueueList)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Producto')
                                    ->columnSpan(4),

                                TextEntry::make('lote.codigo_lote')
                                    ->label('Lote de Inventario')
                                    ->placeholder('—')
                                    ->columnSpan(2),

                                TextEntry::make('cantidad_devolver')
                                    ->label('Cant. Devuelta')
                                    ->weight('bold')
                                    ->color('danger')
                                    ->columnSpan(2),
                            ])
                            ->columns(8),
                    ]),
            ]);
    }
}
