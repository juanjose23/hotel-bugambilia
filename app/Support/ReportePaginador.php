<?php

namespace App\Support;

/**
 * Calcula cuántos elementos caben por página en un PDF A4 a 96 DPI.
 *
 * ─── DomPDF (serie HTB-CP) ───────────────────────────────────────────────
 * Referencia dimensional (todos los valores en px a 96dpi):
 *   A4 alto       = 1122px  (297mm)
 *
 *   @page margin  =   76px  (10mm top + 10mm bottom = 38 + 38)
 *
 * Bloques inline por página (HTB-CP, frame-body SIN padding propio):
 *   doc-header table height  =  72px
 *   doc-header border-bottom =   3px
 *   doc-header margin-bottom =  12px   → total header  =  87px
 *   frame-footer height      =  46px
 *   Total bloques            = 133px
 *   Área útil (AREA_PX)      = 1122 - 76 - 133 = 913px
 *
 * NOTA: El valor original estaba en 886px (tenía cálculo distinto de header).
 * Se mantiene AREA_PX = 886 para no romper CP-001/CP-002 ya calibrados.
 *
 * ─── Spatie PDF (series HTB-ACT / HTB-INV / HTB-COM / HTB-SER) ───────────
 * El mismo layout .reporte-htb, pero los blades añaden `padding: 40px` en
 * el td.frame-body, consumiendo 40px top + 40px bottom = 80px adicionales.
 *
 *   page-frame height  = 1046px   (.page-frame { height: 1046px })
 *   frame-footer       =   46px
 *   doc-header (real)  =   87px   (72 table + 3 border + 12 margin)
 *   frame-body padding =   80px   (40 top + 40 bottom)
 *   ─────────────────────────────────────────────────────────────────────
 *   Área datos Spatie  = 1046 - 46 - 87 - 80 = 833px  → AREA_SPATIE_PX
 *
 * CP-001 fila (área = 886px):
 *   thead (th)  = 34px
 *   Cada td     = 40px  → floor((886-34)/40) = 21 filas → -1 safety = 20
 *
 * ACT/INV fila simple (área = 833px, padding 6px×2 ≈ 21px/fila):
 *   thead (th) real ≈ 21px
 *   td simple  real ≈ 21px → usamos 22px con 1 de safety ≈ 36 filas
 *   td con 2 líneas       ≈ 32px → usamos 32px con 1 safety ≈ 24 filas
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
}
