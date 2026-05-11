<?php

namespace App\Actions\Catalogos;

use App\Models\Catalogos\Producto;
use App\Support\ReportePaginador;
use App\UseCases\Reportes\RegistrarAuditoriaReporteUseCase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Picqer\Barcode\BarcodeGeneratorPNG;

class GenerarReporteProductosAction
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function ejecutar(array $filtros = [], bool $incluirVariantes = true): \Barryvdh\DomPDF\PDF
    {
        $query = Producto::with(['variantes', 'imagen', 'categoria', 'marca']);

        // Aplicar filtros
        if (! empty($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }
        if (! empty($filtros['marca_id'])) {
            $query->where('marca_id', $filtros['marca_id']);
        }
        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }
        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (! empty($filtros['id'])) {
            $query->where('id', $filtros['id']);
        }

        $productos = $query->orderBy('categoria_id')->orderBy('nombre')->get();

        $generator = new BarcodeGeneratorPNG;

        // Mapeo de tipos
        $tiposMapeo = [
            1 => 'PERECEDERO',
            2 => 'NO PERECEDERO',
        ];

        // Procesar productos
        foreach ($productos as $p) {
            $p->tipo_nombre = $tiposMapeo[$p->tipo] ?? 'N/A';

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
            /** @var Collection<int, array{tipo: string, producto: Producto, v?: \App\Models\Catalogos\ProductoVariante|null}> $filasCollection */
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

        $pdf = Pdf::loadView('reportes.productos-variantes', [
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
        $auditoria->ejecutar($codigoReporte, $filtros);

        return $pdf;
    }
}
