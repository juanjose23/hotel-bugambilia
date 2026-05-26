<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\RegistroIndividualizacion\Pages;

use App\Filament\Resources\Activos\RegistroIndividualizacion\RegistroIndividualizacionResource;
use Filament\Resources\Pages\ListRecords;

class ListRegistroIndividualizaciones extends ListRecords
{
    protected static string $resource = RegistroIndividualizacionResource::class;
}
