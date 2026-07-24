<?php

declare(strict_types=1);

namespace App\Filament\Resources\Estancias\CuentaEstanciaResource\Pages;

use App\Filament\Resources\Estancias\CuentaEstanciaResource;
use Filament\Resources\Pages\ListRecords;

final class ListCuentasEstancia extends ListRecords
{
    protected static string $resource = CuentaEstanciaResource::class;
}
