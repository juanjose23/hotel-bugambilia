<?php

declare(strict_types=1);

namespace App\Filament\Shared\Columns;

use BackedEnum;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class EstadoBadgeColumn
{
    /**
     * @param  class-string  $enumClass
     */
    public static function make(string $enumClass, string $column = 'estado'): TextColumn
    {
        return TextColumn::make($column)
            ->label('Estado')
            ->badge()
            ->color(fn ($state) => method_exists($enumClass, 'colorFor') ? ($enumClass::colorFor($state) ?? 'gray') : 'gray')
            ->formatStateUsing(function ($state) use ($enumClass): string {
                if (class_exists($enumClass) && method_exists($enumClass, 'labelFor')) {
                    $label = $enumClass::labelFor($state);
                    if ($label !== '') {
                        return $label;
                    }
                }

                return match (true) {
                    $state instanceof BackedEnum => (string) $state->value,
                    $state instanceof UnitEnum => $state->name,
                    default => (string) ($state ?? ''),
                };
            });
    }
}
