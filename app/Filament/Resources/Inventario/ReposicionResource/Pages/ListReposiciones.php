<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ReposicionResource\Pages;

use App\Filament\Resources\Inventario\ReposicionResource\ReposicionResource;
use Filament\Resources\Pages\ListRecords;

class ListReposiciones extends ListRecords
{
    protected static string $resource = ReposicionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
