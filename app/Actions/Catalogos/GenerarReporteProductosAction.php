<?php

declare(strict_types=1);

namespace App\Actions\Catalogos;

use App\DTOs\Catalogos\ProductosReporteFiltro;
use App\Enums\Catalogos\TipoProducto;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Support\ReportePaginador;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Picqer\Barcode\BarcodeGeneratorPNG;

class GenerarReporteProductosAction
{
    /**
     * @param  array<string, mixed>|ProductosReporteFiltro  $filtros
     */
    public function ejecutar(array|ProductosReporteFiltro $filtros = [], bool $incluirVariantes = true): \Barryvdh\DomPDF\PDF
    {
        $filtroDto = $filtros instanceof ProductosReporteFiltro
            ? $filtros
            : ProductosReporteFiltro::fromArray($filtros);

        $query = Producto::with(['variantes', 'imagen', 'categoria', 'marca']);

        // Aplicar filtros
        if ($filtroDto->categoriaId !== null) {
            $query->where('categoria_id', $filtroDto->categoriaId);
        }
        if ($filtroDto->marcaId !== null) {
            $query->where('marca_id', $filtroDto->marcaId);
        }
        if ($filtroDto->tipo !== null) {
            $query->where('tipo', $filtroDto->tipo);
        }
        if ($filtroDto->estado !== null) {
            $query->where('estado', $filtroDto->estado);
        }
        if ($filtroDto->id !== null) {
            $query->where('id', $filtroDto->id);
        }

        $productos = $query->orderBy('categoria_id')->orderBy('nombre')->get();

        $generator = new BarcodeGeneratorPNG;

        // Procesar productos
        foreach ($productos as $p) {
            $p->tipo_nombre = strtoupper(TipoProducto::labelFor($p->tipo));

            // Cargar imagen de producto en base64 para evitar problemas de rutas
            if ($p->imagen) {
                $imgPath = storage_path('app/public/'.$p->imagen->url);
                if (file_exists($imgPath)) {
                    $imgType = pathinfo($imgPath, PATHINFO_EXTENSION);
                    $p->img_base64 = 'data:image/'.$imgType.';base64,'.base64_encode(file_get_contents($imgPath));
                }
            }

            if ($incluirVariantes) {
                foreach ($p->variantes as $v) {
                    if (! empty($v->codigo)) {
                        try {
                            $v->barcode_base64 = base64_encode($generator->getBarcode($v->codigo, $generator::TYPE_CODE_128));
                        } catch (\Throwable $e) {
                            $v->barcode_base64 = null;
                        }
                    }
                }
            }
        }

        $codigoReporte = $incluirVariantes ? 'HTB-CP-002' : 'HTB-CP-001';
        $nombreReporte = $incluirVariantes ? 'Catálogo Detallado de Productos' : 'Lista General de Productos';

        // Cargar logo en base64 para máxima compatibilidad
        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($logoPath));
        }

        // ── CP-001: simple list ────────────────────────────────────────────
        // ── CP-002: catálogo con variantes ────────────────────────────────
        // Para CP-002 NO usamos chunk() fijo porque cada card tiene altura variable
        // según cuántas variantes tiene. Si usamos chunk() fijo, una card con muchas
        // variantes desborda la página y DomPDF rompe SIN header/footer.
        //
        // Solución: pre-calcular la altura estimada de cada card y agrupar
        // en páginas asegurándonos de que todo cabe dentro de los 886px disponibles.
        //
        //   card header (pcard-hdr)  ≈ 43px
        //   variant thead            = 34px
        //   cada fila de variante    = 38px
        //   margin-bottom de card    = 12px
        //
        // Si una card individual es más alta que la página entera (muchas variantes),
        // va sola y puede hacer overflow — no hay forma de evitarlo con HTML/CSS puro.

        if ($incluirVariantes) {
            // ── CP-002: Tabla plana agrupada ───────────────────────────────────
            // Aplanamos productos+variantes en filas tipadas:
            //   grupo:    fila-separador de producto (~28px)
            //   variante: fila de datos con bc-img-sm 24px (~40px)
            // Chunk fijo de 20 filas → no hay cálculo de altura variable.
            $filas = [];
            foreach ($productos as $p) {
                $filas[] = ['tipo' => 'grupo', 'producto' => $p];
                if ($p->variantes->isEmpty()) {
                    $filas[] = ['tipo' => 'variante', 'producto' => $p, 'v' => null];
                } else {
                    foreach ($p->variantes as $v) {
                        $filas[] = ['tipo' => 'variante', 'producto' => $p, 'v' => $v];
                    }
                }
            }
            /** @var Collection<int, array{tipo: string, producto: Producto, v?: ProductoVariante|null}> $filasCollection */
            $filasCollection = collect($filas);

            $paginas = [];
            foreach ($filasCollection->chunk(20) as $chunk) {
                $paginas[] = $chunk->values();
            }
        } else {
            // ── CP-001: lista simple ───────────────────────────────────────
            $filasPorPagina = ReportePaginador::filasPorPagina(theadPx: 34, rowPx: 40);
            $paginas = $productos->chunk($filasPorPagina)
                ->map(fn ($chunk) => $chunk->values())
                ->values()
                ->all();
        }

        $pdf = Pdf::loadView('reports.catalogos.productos-variantes', [
            'paginas' => $paginas,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name ?? 'Sistema',
            'incluirVariantes' => $incluirVariantes,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'logo_base64' => $logoBase64,
        ]);

        // Registrar auditoría
        $auditoria = new RegistrarAuditoriaReporteUseCase;
        $auditoria->ejecutar($codigoReporte, $filtroDto->toArray());

        return $pdf;
    }
}
