<?php

declare(strict_types=1);

namespace App\Filament\Resources\Estancias\EstanciaResource\Pages;

use App\Filament\Resources\Estancias\EstanciaResource;
use Filament\Resources\Pages\ListRecords;

final class ListEstancias extends ListRecords
{
    protected static string $resource = EstanciaResource::class;
}
