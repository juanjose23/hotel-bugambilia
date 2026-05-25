<?php

namespace App\Filament\Resources\Catalogos\Politicas\Pages;

use App\Filament\Resources\Catalogos\Politicas\PoliticasResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPoliticas extends ViewRecord
{
    protected static string $resource = PoliticasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
