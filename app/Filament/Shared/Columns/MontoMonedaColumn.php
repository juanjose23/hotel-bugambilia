<?php

declare(strict_types=1);

namespace App\Filament\Shared\Columns;

use App\Repository\Models\Monedas\Moneda;
use App\Support\MonedaHelper;
use Closure;
use Filament\Tables\Columns\TextColumn;

class MontoMonedaColumn
{
    /**
     * @param  Closure(mixed): mixed|null  $resolverMoneda
     */
    public static function make(string $column, ?Closure $resolverMoneda = null): TextColumn
    {
        return TextColumn::make($column)
            ->money(fn ($record): string => self::resolverCodigo($record, $resolverMoneda))
            ->sortable();
    }

    private static function resolverCodigo(mixed $record, ?Closure $resolverMoneda): string
    {
        if ($resolverMoneda !== null) {
            return MonedaHelper::codigo(self::instancia($resolverMoneda($record)));
        }

        return MonedaHelper::codigo(self::instancia(
            data_get($record, 'moneda')
                ?? data_get($record, 'cuenta.moneda')
                ?? data_get($record, 'reserva.moneda')
                ?? data_get($record, 'transaccion.moneda'),
        ));
    }

    private static function instancia(mixed $moneda): ?Moneda
    {
        return $moneda instanceof Moneda ? $moneda : null;
    }
}
