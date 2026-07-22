<?php

declare(strict_types=1);

namespace App\Exports\Activos;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

readonly class ActivosExport implements FromCollection
{
    public function collection(): Collection
    {
        return collect();
    }
}
