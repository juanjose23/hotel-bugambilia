<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms\Limpieza;

use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesDestinoLavanderia;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;

final class OrigenLavanderiaSelects
{
    public static function tipo(string $column = 'tipo_origen'): Select
    {
        return Select::make($column)
            ->label('Tipo de Origen')
            ->placeholder('Seleccione tipo de origen')
            ->options([
                'habitacion' => 'Habitación',
                'espacio' => 'Espacio Común (Gym, Spa, Alberca, etc.)',
                'ubicacion' => 'Bodega / Almacén',
                'carrito' => 'Carrito de Limpieza',
            ])
            ->required()
            ->live()
            ->native(false)
            ->afterStateUpdated(function (mixed $state, mixed $set): void {
                if (is_callable($set)) {
                    $set('origen_id', null);
                    $set('categoria_id', null);
                    $set('items', []);
                }
            });
    }

    public static function origen(string $column = 'origen_id', string $tipoColumn = 'tipo_origen'): Select
    {
        return Select::make($column)
            ->label('Origen Específico')
            ->placeholder('Seleccione habitación, espacio, almacén o carrito')
            ->options(function (Get $get) use ($tipoColumn): array {
                $tipo = $get($tipoColumn);

                return app(ObtenerOpcionesDestinoLavanderia::class)->execute(is_string($tipo) ? $tipo : '');
            })
            ->required()
            ->live()
            ->searchable()
            ->preload()
            ->native(false)
            ->afterStateUpdated(function (mixed $state, mixed $set): void {
                if (is_callable($set)) {
                    $set('categoria_id', null);
                    $set('items', []);
                }
            });
    }
}
