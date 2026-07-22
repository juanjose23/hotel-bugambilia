<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Pages;

use App\Filament\Resources\Restaurante\PlatoResource\PlatoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPlato extends EditRecord
{
    protected static string $resource = PlatoResource::class;

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
