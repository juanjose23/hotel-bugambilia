<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PagoConciliacionResource;

use App\Enums\Facturacion\EstadoConciliacionPago;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Facturacion\PagoConciliacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class PagoConciliacionResource extends Resource
{
    protected static ?string $model = PagoConciliacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

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
            ->modifyQueryUsing(fn ($query) => $query->with('transaccion.moneda'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('transaccion.referencia_interna')->label('Transaccion')->searchable()->copyable(),
                EstadoBadgeColumn::make(EstadoConciliacionPago::class),
                MontoMonedaColumn::make('monto_esperado'),
                MontoMonedaColumn::make('monto_recibido'),
                MontoMonedaColumn::make('diferencia'),
                TextColumn::make('conciliada_at')->dateTime()->placeholder('-'),
                TextColumn::make('conciliador.name')->label('Conciliado por')->placeholder('-'),
            ])
            ->filters([
                FiltroEstado::make(EstadoConciliacionPago::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagoConciliaciones::route('/'),
        ];
    }
}
