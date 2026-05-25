<?php

namespace App\Filament\Resources\Catalogos\Politicas\Pages;

use App\Filament\Resources\Catalogos\Politicas\PoliticasResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPoliticas extends EditRecord
{
    protected static string $resource = PoliticasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
