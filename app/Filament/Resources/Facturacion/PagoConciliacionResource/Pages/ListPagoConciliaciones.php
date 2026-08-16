<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PagoConciliacionResource\Pages;

use App\Filament\Resources\Facturacion\PagoConciliacionResource\PagoConciliacionResource;
use Filament\Resources\Pages\ListRecords;

final class ListPagoConciliaciones extends ListRecords
{
    protected static string $resource = PagoConciliacionResource::class;
}
