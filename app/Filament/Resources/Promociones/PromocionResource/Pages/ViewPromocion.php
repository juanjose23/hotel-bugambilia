<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\PromocionResource\Pages;

use App\Filament\Resources\Promociones\PromocionResource\PromocionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPromocion extends ViewRecord
{
    protected static string $resource = PromocionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
