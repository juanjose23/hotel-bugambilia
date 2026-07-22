<?php

declare(strict_types=1);

namespace App\Filament\Shared\Filters;

use Filament\Tables\Filters\TrashedFilter;

class FiltroEliminados
{
    public static function make(): TrashedFilter
    {
        return TrashedFilter::make()
            ->label('Eliminados')
            ->native(false);
    }
}
