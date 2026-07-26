<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CuentaResource\Pages;

use App\Filament\Resources\Cuentas\CuentaResource;
use Filament\Resources\Pages\ListRecords;

final class ListCuentas extends ListRecords
{
    protected static string $resource = CuentaResource::class;
}
