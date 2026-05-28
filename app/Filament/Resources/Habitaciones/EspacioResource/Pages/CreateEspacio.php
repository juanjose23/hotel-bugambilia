<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Pages;

use App\Filament\Resources\Habitaciones\EspacioResource\EspacioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEspacio extends CreateRecord
{
    protected static string $resource = EspacioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
