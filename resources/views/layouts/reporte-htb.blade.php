<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('report_code') – @yield('report_name')</title>
    <style>
        /*
         * DomPDF 3.x – Estrategia SIN position:fixed.
         *
         * @page margin define los márgenes del papel.
         * El body padding es respaldo para garantizar márgenes laterales.
         * Cada "página" es una tabla de altura fija que empuja el footer al fondo.
         *
         * Cálculo A4 @ 96dpi:
         *   Altura total   = 1122px
         *   @page margin v = 10mm * 2 = 76px  →  contenido = 1046px
         *   Anchura total  = 794px
         *   @page margin h = 18mm * 2 = 136px →  contenido ≈ 658px
         */
        @page { margin: 10mm 18mm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1a202c;
            background: #fff;
            /* Padding lateral como respaldo si @page margin no aplica */
            padding: 0 2px;
        }

        /* ── Página contenedora: NO tiene page-break propio para evitar página final en blanco ── */
        .report-page { width: 100%; }
        /* Solo las páginas intermedias reciben esta clase desde Blade ── */
        .page-break   { page-break-after: always; }

        /* Tabla que fuerza el footer al fondo de la página */
        .page-frame {
            width: 100%;
            border-collapse: collapse;
            /* Altura = contenido A4 - márgenes @page verticales */
            height: 1046px;
        }
        .page-frame > tbody > tr > td { padding: 0; border: none; }
        .frame-body   { vertical-align: top; }
        .frame-footer { vertical-align: bottom; height: 46px; }

        /* ── Header corporativo ── */
        .doc-header {
            width: 100%;
            border-bottom: 3px solid #711C37;
            margin-bottom: 12px;
        }
        .doc-header table    { width: 100%; border-collapse: collapse; height: 88px; }
        .doc-header table td { border: none; vertical-align: middle; padding: 0 6px; }

        .hdr-logo  { height: 64px; display: block; }
        .hdr-title { font-size: 17px; font-weight: bold; color: #711C37; text-transform: uppercase; line-height: 1.2; }
        .hdr-code  { font-size: 11px; font-weight: bold; color: #444; font-family: 'Courier New', monospace; margin-top: 4px; }
        .hdr-sub   { font-size: 8px; color: #999; margin-top: 4px; }

        /* ── Footer corporativo ── */
        .doc-footer {
            width: 100%;
            border-top: 2px solid #e2e8f0;
            padding-top: 6px;
        }
        .doc-footer table    { width: 100%; border-collapse: collapse; }
        .doc-footer table td { border: none; vertical-align: middle; padding: 0 4px; font-size: 9px; color: #718096; }

        /* ── Tablas de datos ── */
        .data-table                       { width: 100%; border-collapse: collapse; }
        .data-table tr                    { page-break-inside: avoid; }
        .data-table th {
            background: #711C37;
            color: #fff;
            padding: 8px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            border: 1px solid #5a1530;
        }
        .data-table td                    { padding: 8px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .data-table tr:nth-child(even) td { background: #f8fafc; }

        /* ── Cards CP-002 (ahora tabla plana) ── */
        .pcard               { width: 100%; margin-bottom: 12px; border: 1px solid #c9a3af; page-break-inside: avoid; }
        .pcard-hdr           { background: #f8fafc; border-bottom: 2px solid #711C37; padding: 8px 12px; }
        .pcard-hdr table     { width: 100%; border-collapse: collapse; }
        .pcard-hdr table td  { border: none; vertical-align: middle; padding: 0; }
        .pcard-title         { font-size: 13px; font-weight: bold; color: #711C37; margin-bottom: 3px; }
        .pcard-meta          { font-size: 9px; color: #4a5568; line-height: 1.5; }

        /* ── Tabla agrupada CP-002: fila de grupo (cabecera de producto) ── */
        .grupo-hdr  { background: #f1f5f9; padding: 6px 10px !important; border-left: 4px solid #711C37 !important; }
        .grupo-name { font-size: 11px; font-weight: bold; color: #711C37; margin-right: 8px; }
        .grupo-meta { font-size: 8px; color: #64748b; }

        /* Imagen de código de barras compacta para filas de tabla ── */
        .bc-img-sm  { width: 90px; height: 24px; display: block; margin: 0 auto; }

        /* ── Misc ── */
        .badge     { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-on  { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-off { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border: 1px solid #ddd; }
        .sku-code  { font-weight: bold; font-size: 11px; color: #711C37; font-family: 'Courier New', monospace; }
        .bc-img    { width: 110px; height: 36px; }
        .bc-code   { font-size: 7px; font-family: 'Courier New', monospace; margin-top: 2px; color: #555; }

        @yield('extra-css')
    </style>
</head>
<body>
@yield('content')
</body>
</html>
