<?php

declare(strict_types=1);

namespace App\Support;

use App\Support\Pdf\Calculadores\CalculadorAltura;
use App\Support\Pdf\ConfiguracionPagina;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use Illuminate\Support\Collection;

final readonly class ReportePaginador
{
    private LayoutPdf $layout;

    public function __construct(?LayoutPdf $layout = null)
    {
        $this->layout = $layout ?? new LayoutPdf;
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $items
     * @return array<int, Collection<TKey, TValue>>
     */
    public function paginar(
        Collection $items,
        TiposReporte $tipo,
        ?CalculadorAltura $calculador = null,
        int $altoExtraPrimeraPaginaMm = 0,
    ): array {
        $config = $tipo->configuracion();

        if ($calculador !== null) {
            return $this->paginarConAlturaVariable(
                $items,
                $config->altoFilaMm,
                $calculador,
                $config->altoEncabezadoMm,
                $altoExtraPrimeraPaginaMm,
            );
        }

        if ($tipo === TiposReporte::ETIQUETA) {
            return $this->paginarEtiquetas($items, $config);
        }

        return $this->chunkParaPdf($items, $config->altoFilaMm);
    }

    public function filasPorPagina(
        int $altoFilaMm = 7,
        int $altoEncabezadoMm = 5,
        int $margenSeguridad = 0,
    ): int {
        $disponible = $this->layout->areaUtilMm - $altoEncabezadoMm;
        $filas = (int) floor($disponible / max(1, $altoFilaMm)) - $margenSeguridad;

        return max(1, $filas);
    }

    public function etiquetasPorPagina(
        int $altoEtiquetaMm = 29,
        int $columnas = 3,
    ): int {
        $filas = (int) floor($this->layout->areaUtilMm / max(1, $altoEtiquetaMm));

        return max(1, $filas) * $columnas;
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $items
     * @return array<int, Collection<TKey, TValue>>
     */
    public function chunkParaPdf(
        Collection $items,
        int $altoFilaMm = 7,
        int $altoExtraPrimeraPaginaMm = 0,
    ): array {
        $filasTotales = $this->filasPorPagina(altoFilaMm: $altoFilaMm);

        if ($altoExtraPrimeraPaginaMm <= 0 || $filasTotales <= 1) {
            return $this->dividirEnPaginas($items, $filasTotales);
        }

        $altoFila = max(1, $altoFilaMm);
        $disponible = $this->layout->areaUtilMm - 5;
        $filasPrimeraPagina = max(1, (int) floor(($disponible - $altoExtraPrimeraPaginaMm) / $altoFila));

        if ($items->count() <= $filasPrimeraPagina) {
            return [$items->values()];
        }

        return $this->dividirConPrimeraPagina($items, $filasPrimeraPagina, $filasTotales);
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $items
     * @return array<int, Collection<TKey, TValue>>
     */
    private function paginarEtiquetas(Collection $items, ConfiguracionPagina $config): array
    {
        $elementosPorPagina = $this->etiquetasPorPagina(
            altoEtiquetaMm: $config->altoFilaMm,
            columnas: $config->columnas ?? 3,
        );

        return $this->dividirEnPaginas($items, $elementosPorPagina);
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $items
     * @return array<int, Collection<TKey, TValue>>
     */
    private function dividirEnPaginas(Collection $items, int $elementosPorPagina): array
    {
        return $items->chunk($elementosPorPagina)
            ->map(fn ($c) => $c->values())
            ->values()
            ->all();
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $items
     * @return array<int, Collection<TKey, TValue>>
     */
    private function dividirConPrimeraPagina(Collection $items, int $filasPrimeraPagina, int $filasTotales): array
    {
        $primeraPagina = $items->take($filasPrimeraPagina)->values();
        $resto = $items->slice($filasPrimeraPagina)->values();

        return array_merge(
            [$primeraPagina],
            $this->dividirEnPaginas($resto, $filasTotales),
        );
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $items
     * @return array<int, Collection<TKey, TValue>>
     */
    private function paginarConAlturaVariable(
        Collection $items,
        int $altoBase,
        CalculadorAltura $calculador,
        int $altoEncabezadoMm = 9,
        int $altoExtraPrimeraPaginaMm = 0,
    ): array {
        $disponible = max(1, $this->layout->areaUtilMm - $altoEncabezadoMm);
        $paginas = [];
        $paginaActual = [];
        $acumulado = 0;
        $esPrimeraPagina = true;

        foreach ($items as $item) {
            $alto = max($altoBase, $calculador->altura($item));

            $disponibleActual = $this->disponibleActual(
                $disponible,
                $altoExtraPrimeraPaginaMm,
                $esPrimeraPagina,
            );

            if ($acumulado + $alto > $disponibleActual && ! empty($paginaActual)) {
                $paginas[] = collect($paginaActual);
                $paginaActual = [];
                $acumulado = 0;
                $esPrimeraPagina = false;
            }

            $paginaActual[] = $item;
            $acumulado += $alto;
        }

        if (! empty($paginaActual)) {
            $paginas[] = collect($paginaActual);
        }

        if (empty($paginas)) {
            return [collect()];
        }

        return $paginas;
    }

    private function disponibleActual(int $disponible, int $altoExtraPrimeraPaginaMm, bool $esPrimeraPagina): int
    {
        return $esPrimeraPagina
            ? max(1, $disponible - $altoExtraPrimeraPaginaMm)
            : $disponible;
    }
}
