<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $codigoReporte ?? $codigo ?? 'HTB' }} - {{ $nombreReporte ?? $titulo ?? 'Reporte Oficial' }}</title>
    @php
        $reportMarginLeftMm = $pageMarginLeft ?? \App\Support\Pdf\LayoutPdf::MARGEN_LATERAL_MM;
        $reportMarginRightMm = $pageMarginRight ?? \App\Support\Pdf\LayoutPdf::MARGEN_LATERAL_MM;
        $reportPageSize = strtolower((string) ($pageSize ?? 'letter'));
        $reportOrientation = strtolower((string) ($orientation ?? 'portrait'));
        $reportBaseWidthMm = match ($reportPageSize) {
            'a4' => 210,
            default => 216,
        };
        $reportBaseHeightMm = match ($reportPageSize) {
            'a4' => 297,
            'legal' => 356,
            default => 279,
        };
        $reportPageWidthMm = $reportOrientation === 'landscape' ? $reportBaseHeightMm : $reportBaseWidthMm;
        $reportContentWidthMm = $pageContentWidth ?? max(1, $reportPageWidthMm - $reportMarginLeftMm - $reportMarginRightMm);
    @endphp
         {{-- Preload LCP hero image --}}
         
        <link rel="icon" href="/images/hotel-icon.webp" type="image/webp">
        <link rel="icon" href="/images/hotel-icon.png" type="image/png">
        <link rel="preload" as="image" type="image/webp" href="/images/hero-main.webp" fetchpriority="high">
    <style>
        @page {
            size: {{ $pageSize ?? 'letter' }} {{ $orientation ?? 'portrait' }};
            margin-top: 5mm;
            margin-bottom: 5mm;
            margin-left: 0;
            margin-right: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1e293b;
            background-color: #ffffff;
        }

        .header-fixed {
            position: fixed;
            top: 3mm;
            left: {{ $reportMarginLeftMm }}mm;
            width: {{ $reportContentWidthMm }}mm;
            height: {{ $headerHeight ?? 29 }}mm;
            background-color: #ffffff;
            z-index: 10;
        }

        .footer-fixed {
            position: fixed;
            bottom: 0;
            left: {{ $reportMarginLeftMm }}mm;
            width: {{ $reportContentWidthMm }}mm;
            height: {{ $footerHeight ?? 14 }}mm;
            background-color: #ffffff;
            z-index: 10;
        }

        .report-content,
        .pagina {
            width: 100%;
        }

        main {
            padding-top: {{ ($headerHeight ?? 29) + 3 }}mm;
            margin-left: {{ $reportMarginLeftMm }}mm;
            width: {{ $reportContentWidthMm }}mm;
            padding-bottom: {{ $footerHeight ?? 14 }}mm;
        }

        .pagina {
            padding-top: 0;
            padding-bottom: 0;
        }

        .report-content .pagina {
            padding-top: 0;
            padding-bottom: 0;
        }

        .page-top-spacer {
            height: {{ ($headerHeight ?? 29) + 3 }}mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        .page-break {
            page-break-after: always;
        }

        .filtros-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 7.5pt;
            color: #475569;
            page-break-inside: avoid;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #b8c4d4;
            margin-top: 3mm;
            margin-bottom: 4mm;
        }

        .data-table th {
            background-color: #711C37;
            color: #ffffff;
            padding: 7px 8px;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            border: 1px solid #5a1530;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.25;
        }

        .data-table td {
            padding: 6px 7px;
            border: 1px solid #c5d0df;
            font-size: 8.25pt;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.25;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f3f6fa;
        }

        .data-table tr:nth-child(odd) td {
            background-color: #ffffff;
        }

        .sku-code {
            font-weight: bold;
            font-size: 8.5pt;
            color: #711C37;
            font-family: 'Courier New', monospace;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-warning { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-danger  { background-color: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }
        .badge-info    { background-color: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }

        .amount { text-align: right; white-space: nowrap; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .positive { color: #047857; font-weight: bold; }
        .danger { color: #b91c1c; font-weight: bold; }
        .empty-row { text-align: center; color: #64748b; padding: 12px; font-size: 8.25pt; }
        .total-box { margin-top: 10px; padding: 8px 10px; border: 1px solid #c5d0df; background: #f3f6fa; text-align: right; font-size: 8.75pt; page-break-inside: avoid; }
        .total-box strong { color: #711C37; }
        .section-title { font-size: 9pt; font-weight: bold; color: #711C37; margin-top: 10px; margin-bottom: 5px; }

        @yield('extra-css')
    </style>
</head>
<body>
    <div class="header-fixed">
        @include('reports.layout.partials.header', [
            'logo_base64' => $datosHotel['logo_base64'] ?? $logo_base64 ?? null,
            'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : (is_array($hotelInfo ?? null) ? $hotelInfo : []),
            'nombreReporte' => $nombreReporte ?? $titulo ?? 'Informe Oficial de Control',
            'codigoReporte' => $codigoReporte ?? $codigo ?? 'HTB-REP',
        ])
    </div>

    <div class="footer-fixed">
        @include('reports.layout.partials.footer', [
            'generadoEn' => $generadoEn ?? $datosHotel['generadoEn'] ?? $fecha ?? now()->format('d/m/Y H:i'),
            'usuario' => $usuario ?? $datosHotel['usuario'] ?? auth()->user()?->name ?? 'Sistema',
            'totalRegistros' => $totalRegistros ?? null,
        ])
    </div>

    <main>
        @if((isset($fechaInicio) || isset($fechaFin)) && !($hideFilters ?? false))
            <div class="filtros-box">
                <strong>Periodo Evaluado:</strong>
                Desde: <span style="font-family: monospace; font-weight: bold;">{{ $fechaInicio ?? 'Inicio' }}</span>
                &nbsp;|&nbsp;
                Hasta: <span style="font-family: monospace; font-weight: bold;">{{ $fechaFin ?? 'Actual' }}</span>
                @if(isset($estado) && $estado)
                    &nbsp;|&nbsp; <strong>Estado:</strong> {{ ucfirst((string) $estado) }}
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $font = isset($fontMetrics) ? $fontMetrics->getFont('Helvetica', 'normal') : 'Helvetica';
            $size = 7;
            $color = [0.35, 0.40, 0.50];
            $x = $pdf->get_width() - 90;
            $y = $pdf->get_height() - 24;
            $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, $color);
        }
    </script>
</body>
</html>
