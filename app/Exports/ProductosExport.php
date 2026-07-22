<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductosExport implements FromCollection
{
    public function collection(): Collection
    {
        return collect();
    }
}
