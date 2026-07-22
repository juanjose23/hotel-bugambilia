<?php

declare(strict_types=1);

namespace App\Exports\Servicios;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use stdClass;

class HistoricoPreciosExport implements FromCollection
{
    /** @var Collection<int, stdClass> */
    private Collection $datos;

    /**
     * @param  Collection<int, stdClass>  $datos
     */
    public function __construct(Collection $datos = new Collection)
    {
        $this->datos = $datos;
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function collection(): Collection
    {
        return $this->datos;
    }
}
