<?php

declare(strict_types=1);

namespace App\Filament\Resources\Estancias\CuentaEstanciaResource\Pages;

use App\Filament\Resources\Estancias\CuentaEstanciaResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewCuentaEstancia extends ViewRecord
{
    protected static string $resource = CuentaEstanciaResource::class;

    protected static ?string $title = 'Detalle de cuenta';
}
