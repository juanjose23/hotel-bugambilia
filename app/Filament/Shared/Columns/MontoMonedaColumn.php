<?php

declare(strict_types=1);

namespace App\Filament\Shared\Columns;

use App\Repository\Models\Monedas\Moneda;
use App\Support\MonedaHelper;
use Closure;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

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
            self::resolverValor($record, 'moneda')
                ?? self::resolverValor($record, 'cuenta.moneda')
                ?? self::resolverValor($record, 'reserva.moneda')
                ?? self::resolverValor($record, 'transaccion.moneda'),
        ));
    }

    private static function resolverValor(mixed $record, string $ruta): mixed
    {
        $actual = $record;

        foreach (explode('.', $ruta) as $segmento) {
            if ($actual instanceof Model) {
                if (! $actual->relationLoaded($segmento)) {
                    if (! method_exists($actual, $segmento)) {
                        return null;
                    }

                    $actual->loadMissing($segmento);
                }

                $actual = $actual->getRelation($segmento);

                continue;
            }

            if (is_array($actual)) {
                $actual = $actual[$segmento] ?? null;

                continue;
            }

            if (is_object($actual) && property_exists($actual, $segmento)) {
                $actual = $actual->{$segmento} ?? null;

                continue;
            }

            return null;
        }

        return $actual;
    }

    private static function instancia(mixed $moneda): ?Moneda
    {
        return $moneda instanceof Moneda ? $moneda : null;
    }
}
