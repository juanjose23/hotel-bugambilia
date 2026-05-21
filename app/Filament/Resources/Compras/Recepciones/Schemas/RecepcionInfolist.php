<?php

namespace App\Filament\Resources\Compras\Recepciones\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RecepcionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle de Recepción')
                    ->description('Información del ingreso físico de mercancía')
                    ->icon(Heroicon::ArchiveBox)
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Recepción')
                            ->badge()
                            ->color('primary')
                            ->icon(Heroicon::QrCode),

                        TextEntry::make('ordenCompra.codigo')
                            ->label('Orden de Compra')
                            ->icon(Heroicon::ShoppingCart),

                        TextEntry::make('fecha_recepcion')
                            ->label('Fecha')
                            ->date('d/m/Y')
                            ->icon(Heroicon::Calendar),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge(),

                        TextEntry::make('receptor.name')
                            ->label('Recibido por')
                            ->icon(Heroicon::User),

                        TextEntry::make('guia_remision')
                            ->label('Guía / Factura')
                            ->icon(Heroicon::Hashtag)
                            ->placeholder('—'),

                        TextEntry::make('notas')
                            ->label('Notas de Almacén')
                            ->columnSpanFull()
                            ->placeholder('Sin observaciones'),
                    ]),

                Section::make('Ítems Recibidos')
                    ->icon(Heroicon::QueueList)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Producto')
                                    ->columnSpan(3),

                                TextEntry::make('cantidad_recibida')
                                    ->label('Cant. Recibida')
                                    ->weight('bold')
                                    ->color('success'),

                                TextEntry::make('cantidad_rechazada')
                                    ->label('Cant. Rechazada')
                                    ->color('danger'),

                                TextEntry::make('lote_proveedor')
                                    ->label('Lote Proveedor')
                                    ->placeholder('—'),

                                TextEntry::make('fecha_vencimiento')
                                    ->label('Fecha Venc.')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),

                                TextEntry::make('motivo_rechazo')
                                    ->label('Motivo Rechazo')
                                    ->placeholder('—'),
                            ])
                            ->columns(8),
                    ]),
            ]);
    }
}
