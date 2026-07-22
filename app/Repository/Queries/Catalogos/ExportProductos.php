<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Exports\ProductosExport;
use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportProductos
{
    public function __construct(
        private readonly RegistrarAuditoriaReporte $auditoriaReporte,
    ) {}

    /** @param  array<string, mixed>  $filtros */
    public function exportarCsv(array $filtros = []): string
    {
        $dir = 'exports';
        if (! Storage::disk('private')->exists($dir)) {
            Storage::disk('private')->makeDirectory($dir);
        }

        $filename = 'HTB-CP004-Export-'.now()->format('Ymd_His').'.xlsx';
        $relativeCoverPath = $dir.DIRECTORY_SEPARATOR.$filename;

        Excel::store(new ProductosExport, $relativeCoverPath, 'private');

        $fullPath = Storage::disk('private')->path($relativeCoverPath);

        $this->auditoriaReporte->ejecutar('HTB-CP-004', $filtros, 'exports/'.$filename);

        return $fullPath;
    }
}
