<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\BeneficioClienteResource\Pages;

use App\Filament\Resources\Promociones\BeneficioClienteResource\BeneficioClienteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeneficiosCliente extends ListRecords
{
    protected static string $resource = BeneficioClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
