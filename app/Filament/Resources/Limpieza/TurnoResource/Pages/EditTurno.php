<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\Pages;

use App\Filament\Resources\Limpieza\TurnoResource\TurnoResource;
use Filament\Resources\Pages\EditRecord;

class EditTurno extends EditRecord
{
    protected static string $resource = TurnoResource::class;

    public function getMaxContentWidth(): string
    {
        return '5xl';
    }
}
