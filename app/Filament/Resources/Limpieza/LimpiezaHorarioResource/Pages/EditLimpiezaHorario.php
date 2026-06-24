<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages;

use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\LimpiezaHorarioResource;
use Filament\Resources\Pages\EditRecord;

class EditLimpiezaHorario extends EditRecord
{
    protected static string $resource = LimpiezaHorarioResource::class;

    public function getMaxContentWidth(): string
    {
        return '5xl';
    }
}
