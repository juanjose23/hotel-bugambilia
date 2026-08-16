<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource;

use App\Enums\Cuentas\EstadoVenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Personas\Persona;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Facturación & Finanzas';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $modelLabel = 'Venta';

    protected static ?string $pluralModelLabel = 'Ventas';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'ventas';

    public static function table(Table $table): Table
    {
        return $table
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
                TextColumn::make('subtotal')->label('Subtotal')->money('NIO')->sortable(),
                TextColumn::make('impuesto_total')->label('IVA')->money('NIO'),
                TextColumn::make('total')->label('Total')->money('NIO')->sortable(),
                TextColumn::make('estado')->label('Estado')->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof EstadoVenta ? $state->getLabel() : '')
                    ->color(fn (mixed $state): string => $state instanceof EstadoVenta ? $state->getColor() : 'gray'),
                TextColumn::make('created_at')->label('Emitida')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')->options(EstadoVenta::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentas::route('/'),
        ];
    }
}
