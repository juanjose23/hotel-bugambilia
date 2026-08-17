<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\BeneficioClienteResource\Pages;

use App\Filament\Resources\Promociones\BeneficioClienteResource\BeneficioClienteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBeneficioCliente extends CreateRecord
{
    protected static string $resource = BeneficioClienteResource::class;
}
