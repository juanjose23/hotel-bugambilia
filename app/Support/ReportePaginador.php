<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Calcula cuántos elementos caben por página en un PDF A4 a 96 DPI.
 *
 * AREA_PX = 886px para DomPDF (HTB-CP).
 * AREA_SPATIE_PX = 833px para Spatie PDF (HTB-ACT/INV/COM/SER).
 */
final class ReportePaginador
{
    // A4 a 96dpi
    private const int PAGE_H_PX = 1122;

    // Márgen @page arriba + abajo (10mm × 2 × 3.7795)
    private const int PAGE_MARGIN_V_PX = 76;

    // Bloques inline DomPDF (header 88 + mb12 + mt14 + footer 46 = 160)
    // Mantenido en 160 para que AREA_PX = 886 (ya calibrado en CP-001/002)
    private const int BLOCKS_H_PX = 160;

    // Área neta para reportes DomPDF (HTB-CP series)
    private const int AREA_PX = self::PAGE_H_PX - self::PAGE_MARGIN_V_PX - self::BLOCKS_H_PX; // 886

    /**
     * Área neta para reportes Spatie (HTB-ACT / HTB-INV / HTB-SER).
     *
     * page-frame=1046 - footer(46) - header(87) - framePadding(80) = 833px
     */
    private const int AREA_SPATIE_PX = 833;

    /**
     * Filas de tabla que caben en una página DomPDF (CP-001).
     *
     * @param  int  $theadPx  Altura del encabezado de la tabla (<th>)
     * @param  int  $rowPx  Altura de cada fila de datos (<td>)
     * @param  int  $safety  Filas de margen de seguridad contra desbordamiento
     */
    public static function filasPorPagina(
        int $theadPx = 34,
        int $rowPx = 40,
        int $safety = 1
    ): int {
        $rows = (int) floor((self::AREA_PX - $theadPx) / $rowPx) - $safety;

        return max(1, $rows);
    }

    /**
     * Filas de tabla que caben en una página Spatie con padding:40px (ACT/INV/SER).
     *
     * El área disponible es 833px (ver constante AREA_SPATIE_PX).
     *
     * Guías de rowPx para CSS { padding: 6px 8px; font-size: 9px }:
     *   - Fila de 1 línea:  rowPx ≈ 22px
     *   - Fila de 2 líneas: rowPx ≈ 32px  ← default conservador
     *
     * @param  int  $theadPx  Altura real del thead (th con padding 6px×2 ≈ 21px)
     * @param  int  $rowPx  Altura de cada fila de datos
     * @param  int  $safety  Filas extra de margen de seguridad
     */
    public static function filasPorPaginaSpatie(
        int $theadPx = 21,
        int $rowPx = 32,
        int $safety = 2
    ): int {
        $rows = (int) floor((self::AREA_SPATIE_PX - $theadPx) / $rowPx) - $safety;

        return max(1, $rows);
    }

    /**
     * Etiquetas que caben en una página (CP-003).
     *
     * @param  int  $labelRowPx  Altura real de cada fila de etiquetas (medida del CSS)
     * @param  int  $cols  Columnas de etiquetas por fila
     */
    public static function etiquetasPorPagina(
        int $labelRowPx = 110,
        int $cols = 3
    ): int {
        $rows = (int) floor(self::AREA_PX / $labelRowPx);

        return max(1, $rows) * $cols;
    }

    /**
     * @template T
     *
     * @param  Collection<int, T>  $items
     * @return list<Collection<int, T>>
     */
    public static function chunkParaPdf(Collection $items, int $rowPx = 32): array
    {
        $filasPorPagina = self::filasPorPaginaSpatie(rowPx: $rowPx);

        return $items->chunk($filasPorPagina)
            ->map(fn ($c) => $c->values())
            ->values()
            ->all();
    }
}
