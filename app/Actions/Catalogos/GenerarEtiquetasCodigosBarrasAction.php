<?php

declare(strict_types=1);

namespace App\Actions\Catalogos;

use App\Models\Catalogos\ProductoVariante;
use App\Support\ReportePaginador;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Throwable;

class GenerarEtiquetasCodigosBarrasAction
{
    public function ejecutar(?int $productoId = null): \Barryvdh\DomPDF\PDF
    {
        $query = ProductoVariante::with('producto');

        if ($productoId) {
            $query->where('producto_id', $productoId);
        }

        $variantes = $query->join('productos', 'producto_variantes.producto_id', '=', 'productos.id')
            ->orderBy('productos.nombre')
            ->orderBy('producto_variantes.codigo')
            ->select('producto_variantes.*')
            ->get();

        $generator = new BarcodeGeneratorPNG;
        $etiquetas = [];

        foreach ($variantes as $variante) {
            if (! empty($variante->codigo)) {
                $sku = $variante->codigo;
            }
            if (empty($sku)) {
                continue;
            }

            try {
                $barcodeBase64 = base64_encode($generator->getBarcode($sku, $generator::TYPE_CODE_128));

                $etiquetas[] = [
                    'codigo_barras' => $sku,
                    'imagen' => 'data:image/png;base64,'.$barcodeBase64,
                    'producto' => $variante->producto->nombre,
                    'variante' => $variante->nombre_variante ?? 'Principal',
                    'codigo_completo' => $sku,
                ];
            } catch (Throwable $e) {
                logger($e->getMessage());

                continue;
            }
        }

        // Cargar logo en base64
        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($logoPath));
        }

        /*
         * Estructura de $paginas para la vista:
         *   [
         *     ['filas' => [ [etiqueta, etiqueta, etiqueta], [...] ]],  // página 1
         *     ['filas' => [ ... ]],                                     // página 2
         *   ]
         *
         * etiquetasPorPagina() devuelve el total de etiquetas (ya múltiplo de cols=3).
         * Dividimos en páginas y luego cada página en filas de 3.
         */
        $porPagina = ReportePaginador::etiquetasPorPagina();
        $paginas = collect($etiquetas)
            ->chunk($porPagina)
            ->map(fn (Collection $pagEtiquetas): array => [
                'filas' => $pagEtiquetas->chunk(3)->map(fn (Collection $f): array => $f->values()->all())->values()->all(),
            ])
            ->values()
            ->all();
        $pdf = Pdf::loadView('reports.catalogos.etiquetas-codigos-barras', data: [
            'paginas' => $paginas,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name ?? 'Sistema',
            'codigoReporte' => 'HTB-CP-003',
            'nombreReporte' => 'Etiquetas de Códigos de Barras',
            'logo_base64' => $logoBase64,
        ]);

        $auditoria = new RegistrarAuditoriaReporteUseCase;
        $auditoria->ejecutar('HTB-CP-003', ['producto_id' => $productoId]);

        return $pdf;
    }
}
