<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Pages;

use App\Filament\Resources\Habitaciones\EspacioResource\EspacioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEspacio extends EditRecord
{
    protected static string $resource = EspacioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
