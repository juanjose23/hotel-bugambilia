<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PagoConciliacionResource;

use App\Enums\Facturacion\EstadoConciliacionPago;
use App\Repository\Models\Facturacion\PagoConciliacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PagoConciliacionResource extends Resource
{
    protected static ?string $model = PagoConciliacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Facturación & Finanzas';

    protected static ?string $navigationLabel = 'Conciliacion';

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'facturacion/conciliacion';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('transaccion.referencia_interna')->label('Transaccion')->searchable()->copyable(),
                TextColumn::make('estado')->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof EstadoConciliacionPago ? $state->getLabel() : '')
                    ->color(fn (mixed $state): string => $state instanceof EstadoConciliacionPago ? $state->getColor() : 'gray'),
                TextColumn::make('monto_esperado')->money('NIO')->sortable(),
                TextColumn::make('monto_recibido')->money('NIO')->sortable(),
                TextColumn::make('diferencia')->money('NIO')->sortable(),
                TextColumn::make('conciliada_at')->dateTime()->placeholder('-'),
                TextColumn::make('conciliador.name')->label('Conciliado por')->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('estado')->options(EstadoConciliacionPago::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagoConciliaciones::route('/'),
        ];
    }
}
