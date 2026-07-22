<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\PromocionResource\Pages;

use App\Filament\Resources\Promociones\PromocionResource\PromocionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePromocion extends CreateRecord
{
    protected static string $resource = PromocionResource::class;
}
