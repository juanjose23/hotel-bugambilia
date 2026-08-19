<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Cotizaciones\Schemas;

use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Compras\Cotizacion;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CotizacionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cotización de Proveedor')
                    ->description('Información general de la cotización')
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('solicitud.codigo')
                            ->label('Solicitud')
                            ->icon(Heroicon::DocumentText)
                            ->badge()
                            ->color('primary')
                            ->weight('bold'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge(),

                        TextEntry::make('proveedor.codigo')
                            ->label('Proveedor')
                            ->icon(Heroicon::Identification)
                            ->formatStateUsing(fn ($state, ?Cotizacion $record) => $record && $record->proveedor
                                ? (($record->proveedor->persona && $record->proveedor->persona->personaJuridica)
                                    ? $record->proveedor->persona->personaJuridica->razon_social
                                    : $record->proveedor->codigo)
                                : ''
                            ),

                        TextEntry::make('moneda.codigo')
                            ->label('Moneda')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('fecha_cotizacion')
                            ->label('Fecha Cotización')
                            ->date('d/m/Y')
                            ->icon(Heroicon::Calendar),

                        TextEntry::make('fecha_vencimiento')
                            ->label('Válida hasta')
                            ->date('d/m/Y')
                            ->placeholder('Sin vencimiento'),

                        TextEntry::make('dias_entrega')
                            ->label('Días de Entrega')
                            ->suffix(' días'),

                        TextEntry::make('condicionPago.nombre')
                            ->label('Condición de Pago')
                            ->placeholder('—'),

                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money(fn (?Cotizacion $record) => $record && $record->moneda ? $record->moneda->codigo : 'USD'),

                        TextEntry::make('impuestos')
                            ->label('Impuestos')
                            ->money(fn (?Cotizacion $record) => $record && $record->moneda ? $record->moneda->codigo : 'USD'),

                        TextEntry::make('costo_envio')
                            ->label('Costo Envío')
                            ->money(fn (?Cotizacion $record) => $record && $record->moneda ? $record->moneda->codigo : 'USD'),

                        TextEntry::make('descuento')
                            ->label('Descuento')
                            ->money(fn (?Cotizacion $record) => $record && $record->moneda ? $record->moneda->codigo : 'USD'),

                        TextEntry::make('total')
                            ->label('Total')
                            ->money(fn (?Cotizacion $record) => $record && $record->moneda ? $record->moneda->codigo : 'USD')
                            ->weight('bold')
                            ->color('primary')
                            ->size('lg'),

                        TextEntry::make('es_elegida')
                            ->label('Ganadora')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No'),

                        TextEntry::make('observaciones')
                            ->label('Notas')
                            ->placeholder('Sin notas')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                Section::make('Productos Cotizados')
                    ->description('Detalle de los productos incluidos en la cotización')
                    ->icon(Heroicon::ShoppingBag)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Producto'),

                                TextEntry::make('variante.nombre_variante')
                                    ->label('Variante')
                                    ->placeholder('Estándar'),

                                TextEntry::make('cantidad')
                                    ->label('Cantidad'),

                                TextEntry::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->money(function (mixed $component): string {
                                        if (is_object($component) && method_exists($component, 'getLivewire')) {
                                            $livewire = $component->getLivewire();
                                            if (is_object($livewire) && method_exists($livewire, 'getRecord')) {
                                                $cotizacion = $livewire->getRecord();
                                                if ($cotizacion instanceof Cotizacion && $cotizacion->moneda) {
                                                    return $cotizacion->moneda->codigo;
                                                }
                                            }
                                        }

                                        return 'USD';
                                    }),

                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money(function (mixed $component): string {
                                        if (is_object($component) && method_exists($component, 'getLivewire')) {
                                            $livewire = $component->getLivewire();
                                            if (is_object($livewire) && method_exists($livewire, 'getRecord')) {
                                                $cotizacion = $livewire->getRecord();
                                                if ($cotizacion instanceof Cotizacion && $cotizacion->moneda) {
                                                    return $cotizacion->moneda->codigo;
                                                }
                                            }
                                        }

                                        return 'USD';
                                    })
                                    ->weight('bold'),

                                TextEntry::make('es_elegido')
                                    ->label('Elegido')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Auditoría')
                    ->description('Fechas de registro en el sistema')
                    ->collapsed()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('creadaPor.name')
                            ->label('Registrada por'),
                        TextEntry::make('elegidaPor.name')
                            ->label('Elegida por')
                            ->placeholder('—'),
                        TextEntry::make('elegida_en')
                            ->label('Fecha Selección')
                            ->dateTime()
                            ->placeholder('—'),
                        ...TimestampsInfolistEntry::make(),
                    ]),
            ]);
    }
}
