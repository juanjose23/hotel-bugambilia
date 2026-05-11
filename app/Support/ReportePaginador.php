<?php

namespace App\Support;

/**
 * Calcula cuántos elementos caben por página en un PDF A4 a 96 DPI.
 *
 * Referencia dimensional (todos los valores en px a 96dpi):
 *   A4 alto      = 1122px  (297mm)
 *
 *   @page margin = 76px   (10mm top + 10mm bottom = 38 + 38)
 *
 * Bloques inline por página:
 *   doc-header height       = 88px
 *   doc-header margin-bottom= 12px
 *   doc-footer margin-top   = 14px
 *   doc-footer height       = 46px
 *   Total bloques           = 160px
 *
 *   Área útil para datos   = 1122 - 76 - 160 = 886px
 *
 * CP-001 fila (área = 886px):
 *   thead (th)  = 34px
 *   Cada td     = 40px  → floor((886-34)/40) = 21 filas → -1 safety = 20
 *
 * CP-003 etiqueta (fila de 3 cols, área = 886px):
 *   lbl-prod 12px + 2px margin-bottom
 *   lbl-var  10px + 6px margin-bottom
 *   lbl-bc   44px
 *   lbl-sku  12px + 4px margin-top
 *   cell padding 10px top + 10px bottom
 *   Total fila ≈ 110px  → floor(886/110) = 8 filas × 3 = 24 etiquetas
 */
final class ReportePaginador
{
    // A4 a 96dpi
    private const int PAGE_H_PX = 1122;

    // Márgen @page arriba + abajo (10mm × 2 × 3.7795)
    private const int PAGE_MARGIN_V_PX = 76;

    // Bloques inline: header (88) + margin-bottom (12) + margin-top (14) + footer (46)
    private const int BLOCKS_H_PX = 160;

    // Área neta disponible para contenido de datos
    private const int AREA_PX = self::PAGE_H_PX - self::PAGE_MARGIN_V_PX - self::BLOCKS_H_PX; // 886

    /**
     * Filas de tabla que caben en una página (CP-001).
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
