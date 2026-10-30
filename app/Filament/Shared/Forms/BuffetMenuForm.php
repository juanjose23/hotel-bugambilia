<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Queries\Restaurante\Cocina\MenuBuffetCocinaQuery;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

final class BuffetMenuForm
{
    /** @return list<Component> */
    public static function schema(): array
    {
        return [
            Select::make('tipo_servicio')->extraAttributes(['data-guia' => 'buffet-servicio'])->label('¿Quién sirve los platillos?')->options(['autoservicio' => 'Las personas se sirven (autoservicio)', 'mesero' => 'Un mesero sirve las porciones', 'mixto' => 'Mixto: personas y mesero'])->required()->helperText('Esta opción describe cómo se sirve. La modalidad de cobro se elige por separado.'),
            Repeater::make('menu')->extraAttributes(['data-guia' => 'buffet-menu'])->label('Platillos que ofrecerás')->minItems(1)->defaultItems(1)->reorderable(false)->addActionLabel('Agregar otro platillo')->columns(['default' => 1, 'md' => 3])->schema([
                Select::make('plato_id')->label('Platillo del menú')->options(fn (): array => app(MenuBuffetCocinaQuery::class)->platos())->searchable()->required(),
                Select::make('producto_variante_id')->label('Presentación del platillo terminado')->options(fn (): array => app(MenuBuffetCocinaQuery::class)->variantes())->searchable()->required()->helperText('Elige lo que sale de la preparación, por ejemplo arroz cocido. Los ingredientes crudos se descuentan al cocinar.'),
                TextInput::make('porciones_previstas')->label('Porciones que planeas ofrecer')->numeric()->integer()->minValue(1)->required()->helperText('Es una previsión; el consumo real se mide al cerrar.'),
            ]),
        ];
    }

    /** @param array<array-key, array{plato_id: int|string, producto_variante_id: int|string, porciones_previstas: int|string}> $filas
     * @return list<array{plato_id: int, producto_variante_id: int, porciones_previstas: int}>
     */
    public static function datos(array $filas): array
    {
        return array_values(array_map(fn (array $fila): array => ['plato_id' => (int) $fila['plato_id'], 'producto_variante_id' => (int) $fila['producto_variante_id'], 'porciones_previstas' => (int) $fila['porciones_previstas']], $filas));
    }
}
