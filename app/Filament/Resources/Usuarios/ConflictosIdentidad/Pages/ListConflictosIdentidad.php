<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\ConflictosIdentidad\Pages;

use App\Filament\Resources\Usuarios\ConflictosIdentidad\ConflictoIdentidadResource;
use Filament\Resources\Pages\ListRecords;

class ListConflictosIdentidad extends ListRecords
{
    protected static string $resource = ConflictoIdentidadResource::class;
}
