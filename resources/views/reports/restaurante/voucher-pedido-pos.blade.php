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
        - Voucher POS
    </title>

    <style>
        :root {
            --ticket-width: 80mm;
            --ticket-padding: 4mm;
            --font-primary: "Courier New", Courier, monospace;
            --color-black: #000000;
            --color-gray: #444444;
            --color-light: #aaaaaa;
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
            font-size: 12px;
            line-height: 1.3;
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
            gap: 6px;

            padding: 9px 18px;
            border: 1px solid #334155;
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

        /* Encabezado */
        .ticket-header {
            padding-bottom: 6px;
            border-bottom: 2px solid var(--color-black);
            text-align: center;
        }

        .hotel-name {
            margin: 0 0 2px;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .ticket-title {
            margin: 2px 0;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .ticket-badge {
            display: inline-block;
            margin-top: 3px;
            padding: 2px 6px;
            border: 1px solid var(--color-black);

            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        /* Metadatos */
        .meta-table {
            width: 100%;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed var(--color-black);
        }

        .meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .meta-label {
            width: 38%;
            font-weight: 900;
        }

        /* Ítems */
        .items-table {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .items-table thead th {
            padding-bottom: 3px;
            border-bottom: 1px solid var(--color-black);

            font-size: 11px;
            font-weight: 900;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table tbody td {
            padding: 5px 0;
            border-bottom: 1px dotted var(--color-light);
            vertical-align: top;
        }

        .quantity-column {
            width: 32px;
            text-align: center;
        }

        .item-quantity {
            font-size: 14px;
            font-weight: 900;
            white-space: nowrap;
        }

        .item-name {
            font-size: 13px;
            font-weight: 900;
        }

        .item-note {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 700;
            color: var(--color-gray);
        }

        /* Observaciones generales */
        .general-notes {
            margin-top: 8px;
            padding: 5px;
            border: 1px solid var(--color-black);

            font-size: 11px;
        }

        .general-notes-content {
            white-space: pre-line;
            word-break: break-word;
        }

        /* Total */
        .total-section {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 2px solid var(--color-black);
            text-align: right;
        }

        .total-row {
            font-size: 14px;
            font-weight: 900;
        }

        /* Pie */
        .ticket-footer {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px dashed var(--color-black);
            text-align: center;
        }

        .ticket-footer p {
            margin: 0;
            font-size: 10px;
        }

        @media print {
            html,
            body {
                width: 80mm;
                max-width: 80mm;
            }

            body {
                padding: 0;
                width: 100%;
            }

            .print-actions {
                display: none !important;
            }
        }
    </style>
</head>

<body>
<div class="print-actions">
    <button
        type="button"
        class="btn-print"
        onclick="window.print()"
    >
        Imprimir Comanda POS
    </button>
</div>

<header class="ticket-header">
    <h1 class="hotel-name">
        {{ config('hotel.name', 'Hotel Bugambilias') }}
    </h1>

    <h2 class="ticket-title">
        Comanda Restaurante
    </h2>

    <span class="ticket-badge">
        Pedido #{{ $pedido->codigo }}
    </span>
</header>

<section>
    <table class="meta-table">
        <tr>
            <td class="meta-label">PEDIDO:</td>
            <td><strong>{{ $pedido->codigo }}</strong></td>
        </tr>

        <tr>
            <td class="meta-label">MESA / UBIC.:</td>
            <td><strong>{{ $pedido->mesa->nombre ?? 'Llevar / Habitación' }}</strong></td>
        </tr>

        <tr>
            <td class="meta-label">FECHA HORA:</td>
            <td>{{ $pedido->created_at?->format('d/m/Y H:i:s') }}</td>
        </tr>

        <tr>
            <td class="meta-label">CLIENTE:</td>
            <td>{{ $clienteNombre }}</td>
        </tr>

        @if($meseroNombre !== null)
            <tr>
                <td class="meta-label">MESERO:</td>
                <td>{{ $meseroNombre }}</td>
            </tr>
        @endif

        @if($habitacionNumero !== null)
            <tr>
                <td class="meta-label">HABITACIÓN:</td>
                <td>{{ $habitacionNumero }}</td>
            </tr>
        @endif
    </table>
</section>

<section>
    <table class="items-table">
        <thead>
        <tr>
            <th class="quantity-column">CANT</th>
            <th>DESCRIPCIÓN / DETALLE</th>
        </tr>
        </thead>

        <tbody>
        @foreach($items as $item)
            <tr>
                <td class="item-quantity">
                    x{{ (int) $item->cantidad }}
                </td>

                <td>
                    <div class="item-name">
                        {{ $item->plato->nombre ?? 'Platillo' }}
                    </div>

                    @if(filled($item->observaciones))
                        <div class="item-note">
                            -&gt; OBS: {{ $item->observaciones }}
                        </div>
                    @elseif(filled($item->notas))
                        <div class="item-note">
                            -&gt; NOTA: {{ $item->notas }}
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>

@if(filled($pedido->notas))
    <section class="general-notes">
        <strong>OBSERVACIONES GENERALES:</strong><br>

        <div class="general-notes-content">
            {{ $pedido->notas }}
        </div>
    </section>
@endif

<section class="total-section">
    <div class="total-row">
        TOTAL PEDIDO: {{ $simboloMoneda }} {{ number_format($total, 2) }}
    </div>
</section>

<footer class="ticket-footer">
    <p>{{ strtoupper(config('hotel.name', 'Hotel Bugambilias')) }} POS</p>
    <p>Impresión Automática por Área</p>
</footer>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            window.print();
        }, 300);
    });
</script>
</body>
</html>
