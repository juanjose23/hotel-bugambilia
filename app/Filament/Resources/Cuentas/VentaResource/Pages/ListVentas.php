<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource\Pages;

use App\Filament\Resources\Cuentas\VentaResource\VentaResource;
use Filament\Resources\Pages\ListRecords;

final class ListVentas extends ListRecords
{
    protected static string $resource = VentaResource::class;
}
