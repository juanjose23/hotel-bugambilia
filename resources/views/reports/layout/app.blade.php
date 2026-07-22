<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $codigoReporte ?? 'N/D' }} – {{ $nombreReporte ?? 'Reporte' }}</title>
    <style>
        @page {
            margin: {{ $pageMarginTop ?? 5 }}mm {{ $pageMarginRight ?? 5 }}mm {{ $pageMarginBottom ?? 5 }}mm {{ $pageMarginLeft ?? 5 }}mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; color: #1a202c; background: #fff; }
        .pagina { position: relative; padding: 0 4mm; min-height: calc(100vh - {{ ($pageMarginTop ?? 5) + ($pageMarginBottom ?? 5) }}mm); }
        .report-header { border-bottom: 2px solid #711C37; padding: 1.5mm 0; }
        .report-content { padding-bottom: 10mm; }
        .report-footer {
            position: absolute; bottom: 2mm; left: 4mm; right: 4mm;
            border-top: 1px solid #e2e8f0; padding: 1.5mm 0;
        }
        .page-break { page-break-after: always; }
        .filtros-info { padding: 1.5mm 0; margin-bottom: 2mm; font-size: 8px; color: #4a5568; border-bottom: 1px dashed #d1d5db; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            background: #711C37; color: #fff; padding: 5px 7px; font-size: 8px; font-weight: bold;
            text-transform: uppercase; text-align: left; border: 1px solid #5a1530;
        }
        .data-table td { padding: 4px 7px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .data-table tr:nth-child(even) td { background: #f8fafc; }
        .sku-code { font-weight: bold; font-size: 10px; color: #711C37; font-family: 'Courier New', monospace; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .badge-on { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        @yield('extra-css')
    </style>
</head>
<body>
    @yield('content')

    <script type="text/php">
        if (isset($pdf)) {
            $font = $pdf->getFontMetrics()->getFont('Arial', 'normal');
            $w = $pdf->getW();
            $h = $pdf->getH();
            $pdf->page_text($w / 2 - 25, $h - 75, 'Pág. {PAGE_NUM} de {PAGE_COUNT}', $font, 7, array(0.44, 0.50, 0.59));
        }
    </script>
</body>
</html>
