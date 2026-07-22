<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Pages;

use App\Filament\Resources\Restaurante\PlatoResource\PlatoResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlato extends CreateRecord
{
    protected static string $resource = PlatoResource::class;
}
