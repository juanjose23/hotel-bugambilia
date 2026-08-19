<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Models\Catalogos\Producto;
use App\Support\Excel\ColumnaExcel;
use App\Support\Excel\GeneradorExcel;
use Illuminate\Support\Facades\Storage;

final class ExportProductos
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

        $productos = Producto::with(['categoria'])->get();

        (new GeneradorExcel)->almacenar(
            coleccion: $productos,
            columnas: [
                ColumnaExcel::make('SKU', fn ($p) => $p->sku ?? $p->codigo),
                ColumnaExcel::make('Nombre', fn ($p) => $p->nombre),
                ColumnaExcel::make('Categoría', fn ($p) => $p->categoria->nombre ?? 'N/A'),
                ColumnaExcel::make('Precio Base', fn ($p) => (float) ($p->precio_base ?? 0), numerica: true),
                ColumnaExcel::make('Costo Promedio', fn ($p) => (float) ($p->costo_promedio ?? 0), numerica: true),
            ],
            ruta: $relativeCoverPath,
            disk: 'private',
            hoja: 'Productos',
        );

        $fullPath = Storage::disk('private')->path($relativeCoverPath);

        $this->auditoriaReporte->ejecutar('HTB-CP-004', [
            'filtros' => $filtros,
            'archivo' => 'exports/'.$filename,
        ]);

        return $fullPath;
    }
}
