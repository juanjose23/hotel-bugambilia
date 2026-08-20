<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms\Limpieza;

use App\Repository\Queries\Limpieza\Stock\ObtenerCantidadStock;
use App\Repository\Queries\Limpieza\Stock\ObtenerOpcionesStockPorUbicacion;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

final class StockUbicacionSelect
{
    public static function make(string $column, string $label, Closure $ubicacionId, bool $llenarCantidad = false): Select
    {
        return Select::make($column)
            ->label($label)
            ->placeholder('Seleccione insumo')
            ->options(function (Get $get) use ($ubicacionId): array {
                $resolved = $ubicacionId($get);

                if (is_array($resolved)) {
                    $ubicaciones = array_values(array_filter(
                        array_map(fn (mixed $id): ?int => is_numeric($id) ? (int) $id : null, $resolved),
                        fn (?int $id): bool => $id !== null,
                    ));

                    return app(ObtenerOpcionesStockPorUbicacion::class)->execute($ubicaciones);
                }

                return app(ObtenerOpcionesStockPorUbicacion::class)->execute(is_numeric($resolved) ? (int) $resolved : 0);
            })
            ->required()
            ->live()
            ->native(false)
            ->prefixIcon(Heroicon::ListBullet)
            ->afterStateUpdated(function (?int $state, Set $set) use ($llenarCantidad): void {
                if ($state === null) {
                    $set('max_qty', 0);
                    $set('producto_variante_id', null);

                    return;
                }

                $cantidad = app(ObtenerCantidadStock::class)->execute($state);
                $set('max_qty', $cantidad);

                if ($llenarCantidad) {
                    $set('cantidad', $cantidad);
                }
            });
    }
}
