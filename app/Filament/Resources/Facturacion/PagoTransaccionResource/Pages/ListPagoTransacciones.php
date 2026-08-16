<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PagoTransaccionResource\Pages;

use App\Filament\Resources\Facturacion\PagoTransaccionResource\PagoTransaccionResource;
use Filament\Resources\Pages\ListRecords;

final class ListPagoTransacciones extends ListRecords
{
    protected static string $resource = PagoTransaccionResource::class;
}
