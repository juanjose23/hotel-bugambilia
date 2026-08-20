<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms\Limpieza;

use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesDestinoLavanderia;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;

final class DestinoLavanderiaSelects
{
    public static function tipo(string $column = 'tipo_destino'): Select
    {
        return Select::make($column)
            ->label('Tipo de Destino')
            ->options([
                'habitacion' => 'Habitación',
                'espacio' => 'Espacio Común',
                'ubicacion' => 'Bodega / Almacén Físico',
            ])
            ->required()
            ->live()
            ->native(false);
    }

    public static function destino(string $column = 'destino_id', string $tipoColumn = 'tipo_destino'): Select
    {
        return Select::make($column)
            ->label('Destino Específico')
            ->placeholder('Seleccione destino')
            ->options(function (Get $get) use ($tipoColumn): array {
                $tipo = $get($tipoColumn);

                return app(ObtenerOpcionesDestinoLavanderia::class)->execute(is_string($tipo) ? $tipo : '');
            })
            ->required()
            ->searchable()
            ->native(false);
    }
}
