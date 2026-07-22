<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Filament\Resources\Reservas\ReservaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReservas extends ListRecords
{
    protected static string $resource = ReservaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
