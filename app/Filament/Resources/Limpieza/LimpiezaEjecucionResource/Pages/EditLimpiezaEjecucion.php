<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages;

use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use Filament\Resources\Pages\EditRecord;

class EditLimpiezaEjecucion extends EditRecord
{
    protected static string $resource = LimpiezaEjecucionResource::class;

    public function getMaxContentWidth(): string
    {
        return '5xl';
    }
}
