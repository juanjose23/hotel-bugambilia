<?php

namespace App\UseCases\Catalogos;

use App\Exports\ProductosExport;
use App\UseCases\Reportes\RegistrarAuditoriaReporteUseCase;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportProductosUseCase
{
    /**
     * Exporta productos a un archivo Excel (.xlsx) nativo.
     * Código oficial: HTB-CP-004
     *
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

        // Generar Excel usando la librería oficial
        Excel::store(new ProductosExport($filtros), $relativeCoverPath, 'private');

        $fullPath = Storage::disk('private')->path($relativeCoverPath);

        // Registrar auditoría en español
        $auditoria = new RegistrarAuditoriaReporteUseCase;
        $auditoria->ejecutar('HTB-CP-004', $filtros, 'exports/'.$filename);

        return $fullPath;
    }
}
