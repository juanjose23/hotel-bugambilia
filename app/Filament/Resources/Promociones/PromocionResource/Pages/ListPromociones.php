<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\PromocionResource\Pages;

use App\Filament\Resources\Promociones\PromocionResource\PromocionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromociones extends ListRecords
{
    protected static string $resource = PromocionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
