<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Schemas;

use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OrdenCompraInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Orden de Compra')
                    ->description('Detalles del compromiso de compra')
                    ->icon(Heroicon::ShoppingCart)
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Código')
                            ->icon(Heroicon::QrCode)
                            ->badge()
                            ->color('primary')
                            ->copyable()
                            ->weight('bold'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge(),

                        TextEntry::make('proveedor.codigo')
                            ->label('Proveedor')
                            ->icon(Heroicon::UserGroup)
                            ->formatStateUsing(fn ($state, $record) => "{$record->proveedor->codigo} - ".($record->proveedor->persona->personaJuridica->razon_social ?? $record->proveedor->persona->nombre_completo)),

                        TextEntry::make('solicitud.codigo')
                            ->label('Solicitud Ref.')
                            ->placeholder('Compra Directa')
                            ->icon(Heroicon::DocumentText),

                        TextEntry::make('fecha_orden')
                            ->label('Fecha Orden')
                            ->date('d/m/Y')
                            ->icon(Heroicon::Calendar),

                        TextEntry::make('condicionPago.nombre')
                            ->label('Condición de Pago')
                            ->icon(Heroicon::CreditCard),

                        TextEntry::make('total')
                            ->label('Total')
                            ->money('USD')
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary'),
                    ]),

                Section::make('Productos en la Orden')
                    ->icon(Heroicon::RectangleStack)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Producto')
                                    ->hint(fn ($record) => $record->variante?->codigo ? "Variante: {$record->variante->codigo}" : null)
                                    ->columnSpan(2),

                                TextEntry::make('cantidad')
                                    ->label('Cantidad'),

                                TextEntry::make('unidadMedida.nombre')
                                    ->label('U.M.')
                                    ->placeholder('—'),

                                TextEntry::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->money('USD'),

                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('USD')
                                    ->weight('bold'),
                            ])
                            ->columns(6),
                    ]),

                Section::make('Auditoría')
                    ->collapsed()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        ...TimestampsInfolistEntry::make(),
                    ]),
            ]);
    }
}
