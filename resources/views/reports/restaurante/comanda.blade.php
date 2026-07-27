<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Comanda #{{ $pedido->codigo }}
        - {{ $tipo?->getLabel() ?? 'COMANDA' }}
    </title>

    <style>
        :root {
            --ticket-width: 80mm;
            --ticket-padding: 4mm;
            --font-primary: "Courier New", Courier, monospace;
            --color-black: #000000;
            --color-gray: #444444;
            --color-light: #dddddd;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: var(--color-black);
            font-family: var(--font-primary), serif;
        }

        body {
            width: var(--ticket-width);
            max-width: var(--ticket-width);
            padding: var(--ticket-padding);
            font-size: 11px;
            line-height: 1.35;
        }

        .ticket {
            width: 100%;
        }

        /* Botón para vista web */
        .print-actions {
            margin-bottom: 12px;
            text-align: center;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            padding: 9px 16px;
            border: 0;
            border-radius: 6px;

            background: #0f172a;
            color: #ffffff;

            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: 700;

            cursor: pointer;
        }

        .btn-print:hover {
            background: #1e293b;
        }

        .btn-print svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Utilidades */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .font-bold {
            font-weight: 700;
        }

        .separator {
            width: 100%;
            margin: 7px 0;
            border: 0;
            border-top: 1px dashed var(--color-black);
        }

        .separator-solid {
            border-top-style: solid;
        }

        .separator-double {
            height: 4px;
            border-top: 1px solid var(--color-black);
            border-bottom: 1px solid var(--color-black);
        }

        /* Encabezado */
        .ticket-header {
            text-align: center;
        }

        .hotel-name {
            margin: 0;
            font-size: 17px;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .ticket-title {
            margin: 5px 0 2px;
            font-size: 13px;
            font-weight: 900;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .area-name {
            margin: 2px 0 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .command-type {
            display: inline-block;
            min-width: 65%;
            margin-top: 3px;
            padding: 4px 7px;

            border: 2px solid var(--color-black);

            font-size: 12px;
            font-weight: 900;
            line-height: 1.2;
            text-align: center;
            text-transform: uppercase;
        }

        .command-copy {
            display: block;
            margin-top: 2px;
            font-size: 10px;
            font-weight: 700;
        }

        /* Información del pedido */
        .order-code {
            margin: 8px 0 6px;
            text-align: center;
        }

        .order-code-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .order-code-value {
            display: block;
            margin-top: 1px;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
            word-break: break-word;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
            word-break: break-word;
        }

        .meta-label {
            width: 34%;
            padding-right: 5px !important;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .meta-value {
            width: 66%;
            font-size: 11px;
            font-weight: 600;
        }

        .meta-highlight {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        /* Ítems */
        .items-title {
            margin: 8px 0 5px;
            font-size: 12px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .items-table thead th {
            padding: 3px 2px;
            border-top: 1px solid var(--color-black);
            border-bottom: 1px solid var(--color-black);

            font-size: 10px;
            font-weight: 900;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table tbody td {
            padding: 6px 2px;
            border-bottom: 1px dotted var(--color-gray);
            vertical-align: top;
        }

        .quantity-column {
            width: 15%;
            text-align: center !important;
        }

        .description-column {
            width: 85%;
        }

        .item-quantity {
            font-size: 15px;
            font-weight: 900;
            line-height: 1.1;
            text-align: center;
            white-space: nowrap;
        }

        .item-name {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.25;
            text-transform: uppercase;
            word-break: break-word;
        }

        .item-note {
            margin-top: 4px;
            padding: 3px 4px;
            border-left: 3px solid var(--color-black);

            font-size: 10px;
            font-weight: 700;
            line-height: 1.25;
            text-transform: uppercase;
            word-break: break-word;
        }

        .empty-items {
            padding: 10px 4px !important;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        /* Observaciones */
        .general-notes {
            margin-top: 8px;
            padding: 6px;
            border: 2px solid var(--color-black);
        }

        .general-notes-title {
            display: block;
            margin-bottom: 3px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .general-notes-content {
            font-size: 11px;
            font-weight: 700;
            line-height: 1.3;
            white-space: pre-line;
            word-break: break-word;
        }

        /* Total */
        .total-section {
            margin-top: 9px;
            padding: 7px 0;

            border-top: 2px solid var(--color-black);
            border-bottom: 2px solid var(--color-black);
        }

        .total-label {
            display: block;
            font-size: 10px;
            font-weight: 900;
            text-align: right;
            text-transform: uppercase;
        }

        .total-value {
            display: block;
            margin-top: 1px;
            font-size: 17px;
            font-weight: 900;
            line-height: 1.2;
            text-align: right;
        }

        /* Pie */
        .ticket-footer {
            margin-top: 10px;
            text-align: center;
        }

        .ticket-footer p {
            margin: 2px 0;
        }

        .footer-brand {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .footer-description {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .footer-time {
            margin-top: 5px !important;
            font-size: 9px;
        }

        @media print {
            html,
            body {
                width: 80mm;
                max-width: 80mm;
                background: #ffffff;
            }

            body {
                padding: 3mm;
            }

            .no-print {
                display: none !important;
            }

            .ticket {
                page-break-after: avoid;
            }

            .items-table tr,
            .general-notes,
            .total-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
<div class="no-print print-actions">
    <button
        type="button"
        class="btn-print"
    >
        <svg
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2m2 4h6a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2Zm8-12V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4h10Z"
            />
        </svg>

        Imprimir comanda
    </button>
</div>

<main class="ticket">
    <header class="ticket-header">
        <h1 class="hotel-name">
            Hotel Bugambilias
        </h1>

        <div class="separator-double"></div>

        <h2 class="ticket-title">
            Comanda de producción
        </h2>

        <p class="area-name">
            {{ $area
                ? 'Área: ' . $area->getLabel()
                : 'Restaurante'
            }}
        </p>

        <div class="command-type">
            {{ $tipo?->getLabel() ?? 'Nuevo pedido' }}

            <span class="command-copy">
                    Copia #{{ $pedido->consecutivo_comanda ?? 1 }}
                </span>
        </div>
    </header>

    <section class="order-code">
            <span class="order-code-label">
                Código del pedido
            </span>

        <span class="order-code-value">
                {{ $pedido->codigo }}
            </span>
    </section>

    <hr class="separator separator-solid">

    <section>
        <table class="meta-table">
            <tbody>
            <tr>
                <td class="meta-label">
                    Mesa / ubic.:
                </td>

                <td class="meta-value meta-highlight">
                    {{ $pedido->mesa?->nombre
                        ?? 'Llevar / Habitación'
                    }}
                </td>
            </tr>

            <tr>
                <td class="meta-label">
                    Fecha:
                </td>

                <td class="meta-value">
                    {{ now()->format('d/m/Y') }}
                </td>
            </tr>

            <tr>
                <td class="meta-label">
                    Hora:
                </td>

                <td class="meta-value">
                    {{ now()->format('H:i:s') }}
                </td>
            </tr>

            <tr>
                <td class="meta-label">
                    Cliente:
                </td>

                <td class="meta-value">
                    {{ $pedido->cliente?->nombre_completo
                        ?? (
                            'Cliente '
                            . ($pedido->mesa?->nombre ?? 'Mostrador')
                        )
                    }}
                </td>
            </tr>

            @if($pedido->mesero?->persona)
                <tr>
                    <td class="meta-label">
                        Mesero:
                    </td>

                    <td class="meta-value">
                        {{ $pedido->mesero->persona->nombre_completo }}
                    </td>
                </tr>
            @endif

            @if($pedido->cuenta)
                <tr>
                    <td class="meta-label">
                        Habitación:
                    </td>

                    <td class="meta-value meta-highlight">
                        {{ $pedido->cuenta
                            ?->estancia
                            ?->habitacion
                            ?->numero
                            ?? 'Cuenta habitación'
                        }}
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </section>

    <h3 class="items-title">
        Detalle del pedido
    </h3>

    <section>
        <table class="items-table">
            <thead>
            <tr>
                <th class="quantity-column">
                    Cant.
                </th>

                <th class="description-column">
                    Producto / detalle
                </th>
            </tr>
            </thead>

            <tbody>
            @forelse($items as $item)
                @continue($item->estado?->value === 'cancelado')

                <tr>
                    <td class="item-quantity">
                        {{ (int) $item->cantidad }}×
                    </td>

                    <td>
                        <div class="item-name">
                            {{ $item->plato?->nombre ?? 'Platillo' }}
                        </div>

                        @if(filled($item->observaciones))
                            <div class="item-note">
                                Obs: {{ $item->observaciones }}
                            </div>
                        @elseif(filled($item->notas))
                            <div class="item-note">
                                Nota: {{ $item->notas }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="2"
                        class="empty-items"
                    >
                        Sin ítems para esta área
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>

    @if(filled($pedido->notas))
        <section class="general-notes">
                <span class="general-notes-title">
                    Observaciones generales
                </span>

            <div class="general-notes-content">
                {{ $pedido->notas }}
            </div>
        </section>
    @endif

    <section class="total-section">
            <span class="total-label">
                Total del pedido
            </span>

        <span class="total-value">
                C$ {{ number_format(
                    (float) $pedido->total,
                    2,
                    '.',
                    ','
                ) }}
            </span>
    </section>

    <footer class="ticket-footer">
        <p class="footer-brand">
            Hotel Bugambilias
        </p>

        <p class="footer-description">
            Comanda generada automáticamente por área
        </p>

        <p class="footer-time">
            Impreso: {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </footer>
</main>
</body>
</html>
