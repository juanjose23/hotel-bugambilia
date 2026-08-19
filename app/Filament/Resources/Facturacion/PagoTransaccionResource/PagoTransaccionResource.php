<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PagoTransaccionResource;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Facturacion\PagoTransaccion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PagoTransaccionResource extends Resource
{
    protected static ?string $model = PagoTransaccion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?string $navigationLabel = 'Transacciones';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'facturacion/transacciones';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('referencia_interna')->label('Referencia')->searchable()->copyable()->weight('bold'),
                TextColumn::make('pasarela.nombre')->label('Pasarela'),
                TextColumn::make('reserva.codigo_reserva')->label('Reserva')->searchable()->placeholder('-'),
                TextColumn::make('cuenta.numero_cuenta')->label('Cuenta')->placeholder('-'),
                TextColumn::make('factura.numero')->label('Factura')->placeholder('-'),
                TextColumn::make('moneda.codigo')->label('Moneda'),
                TextColumn::make('monto')->money('NIO')->sortable(),
                TextColumn::make('estado')->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof EstadoTransaccionPago ? $state->getLabel() : '')
                    ->color(fn (mixed $state): string => $state instanceof EstadoTransaccionPago ? $state->getColor() : 'gray'),
                TextColumn::make('referencia_pasarela')->label('Ref. pasarela')->searchable()->placeholder('-'),
                TextColumn::make('capturada_at')->dateTime()->placeholder('-')->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')->options(EstadoTransaccionPago::class),
                SelectFilter::make('pasarela_pago_id')->relationship('pasarela', 'nombre')->label('Pasarela'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagoTransacciones::route('/'),
        ];
    }
}
