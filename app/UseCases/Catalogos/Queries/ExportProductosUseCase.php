<?php

namespace App\UseCases\Catalogos\Queries;

use App\Exports\ProductosExport;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportProductosUseCase
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function exportarCsv(array $filtros = []): string
    {
        $dir = 'exports';
        if (! Storage::disk('private')->exists($dir)) {
            Storage::disk('private')->makeDirectory($dir);
        }

        $filename = 'HTB-CP004-Export-'.now()->format('Ymd_His').'.xlsx';
        $relativeCoverPath = $dir.DIRECTORY_SEPARATOR.$filename;

        Excel::store(new ProductosExport($filtros), $relativeCoverPath, 'private');

        $fullPath = Storage::disk('private')->path($relativeCoverPath);

        $auditoria = new RegistrarAuditoriaReporteUseCase;
        $auditoria->ejecutar('HTB-CP-004', $filtros, 'exports/'.$filename);

        return $fullPath;
    }
}
