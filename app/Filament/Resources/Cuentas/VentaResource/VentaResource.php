<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource;

use App\Enums\Cuentas\EstadoVenta;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Personas\Persona;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Caja & Facturación';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $modelLabel = 'Venta';

    protected static ?string $pluralModelLabel = 'Ventas';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'ventas';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['cliente.persona', 'moneda']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('numero_venta')->label('N° Venta')->searchable()->sortable(),
                TextColumn::make('cuenta.numero_cuenta')->label('Cuenta')->searchable()->placeholder('—'),
                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('cliente', fn (Builder $clienteQuery): Builder => Persona::filtrarPorNombre($clienteQuery, $search)))
                    ->placeholder('—'),
                TextColumn::make('moneda.codigo')->label('Moneda'),
                MontoMonedaColumn::make('subtotal')->label('Subtotal'),
                MontoMonedaColumn::make('impuesto_total')->label('IVA'),
                MontoMonedaColumn::make('total')->label('Total'),
                EstadoBadgeColumn::make(EstadoVenta::class),
                FechaStandardColumn::make('created_at', 'Emitida'),
            ])
            ->filters([
                FiltroEstado::make(EstadoVenta::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentas::route('/'),
        ];
    }
}
