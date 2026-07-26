<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\AuditoriaRestauranteResource\Pages;

use App\Filament\Resources\Restaurante\AuditoriaRestauranteResource\AuditoriaRestauranteResource;
use Filament\Resources\Pages\ListRecords;

final class ListAuditoriaRestaurantes extends ListRecords
{
    protected static string $resource = AuditoriaRestauranteResource::class;
}
