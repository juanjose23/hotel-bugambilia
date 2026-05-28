<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Queries;

use App\Support\ReportePaginador;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Picqer\Barcode\BarcodeGeneratorPNG;

class GenerarEtiquetasActivosUseCase
{
    public function __construct(
        private ObtenerReportesActivosVariosUseCase $obtenerReportesActivosVariosUseCase
    ) {}

    /**
     * @param  array<string, mixed>  $filtros
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(array $filtros = []): array
    {
        $activos = $this->obtenerReportesActivosVariosUseCase->inventarioGeneral($filtros);

        $generator = new BarcodeGeneratorPNG;
        $etiquetas = [];

        foreach ($activos as $activo) {
            $sku = $activo->codigo_inventario;
            if (empty($sku)) {
                continue;
            }

            try {
                $barcodeBase64 = base64_encode($generator->getBarcode($sku, $generator::TYPE_CODE_128));

                $etiquetas[] = [
                    'codigo_barras' => $sku,
                    'imagen' => 'data:image/png;base64,'.$barcodeBase64,
                    'producto' => $activo->producto->nombre ?? 'Activo',
                    'variante' => $activo->nombre_descriptivo ?? 'General',
                ];
            } catch (\Throwable $e) {
                Log::warning($e->getMessage());

                continue;
            }
        }

        $porPagina = ReportePaginador::etiquetasPorPagina();

        return collect($etiquetas)
            ->chunk($porPagina)
            ->map(fn (Collection $pagEtiquetas): array => [
                'filas' => $pagEtiquetas->chunk(3)->map(fn (Collection $f): array => $f->values()->all())->values()->all(),
            ])
            ->values()
            ->all();
    }
}
