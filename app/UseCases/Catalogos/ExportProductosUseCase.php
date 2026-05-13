<?php

namespace App\UseCases\Catalogos;

use App\Exports\ProductosExport;
use App\UseCases\Reportes\RegistrarAuditoriaReporteUseCase;
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
        if (!\Illuminate\Support\Facades\Storage::disk('private')->exists($dir)) {
            \Illuminate\Support\Facades\Storage::disk('private')->makeDirectory($dir);
        }

        $filename = 'HTB-CP004-Export-' . now()->format('Ymd_His') . '.xlsx';
        $relativeCoverPath = $dir . DIRECTORY_SEPARATOR . $filename;
        
        // Generar Excel usando la librería oficial
        Excel::store(new ProductosExport($filtros), $relativeCoverPath, 'private');

        $fullPath = \Illuminate\Support\Facades\Storage::disk('private')->path($relativeCoverPath);

        // Registrar auditoría en español
        $auditoria = new RegistrarAuditoriaReporteUseCase();
        $auditoria->ejecutar('HTB-CP-004', $filtros, 'exports/' . $filename);

        return $fullPath;
    }
}
