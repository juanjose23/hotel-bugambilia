<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

trait InyectaDesdeContenedor
{
    public static function make(): static
    {
        return app(static::class);
    }
}
