<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Roles\Pages;

use App\Filament\Resources\Usuarios\Roles\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
