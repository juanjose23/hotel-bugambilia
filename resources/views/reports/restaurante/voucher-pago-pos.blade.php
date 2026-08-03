<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Voucher Pago {{ $cuenta->numero_cuenta }}
        - Comprobante POS
    </title>

    <style>
        :root {
            --ticket-width: 80mm;
            --ticket-padding: 4mm;
            --font-primary: "Courier New", Courier, monospace;
            --color-black: #000000;
            --color-gray: #444444;
            --color-green-bg: #d1fae5;
            --color-green-text: #065f46;
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
            padding: 8px 6px;
            font-size: 11px;
            line-height: 1.25;
        }

        /* Botón para vista web */
        .print-actions {
            margin-bottom: 10px;
            text-align: center;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;

            padding: 6px 14px;
            border: 1px solid #334155;
            border-radius: 6px;

            background: #0f172a;
            color: #ffffff;

            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: 700;

            cursor: pointer;
        }

        .btn-print:hover {
            background: #1e293b;
        }

        /* Encabezado */
        .ticket-header {
            padding-bottom: 4px;
            border-bottom: 2px solid var(--color-black);
            text-align: center;
        }

        .hotel-name {
            margin: 0 0 1px;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .ticket-title {
            margin: 1px 0;
            font-size: 12px;
            font-weight: 900;
        }

        .ticket-badge {
            display: inline-block;
            margin-top: 2px;
            padding: 1px 5px;
            border: 1px solid var(--color-black);

            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;

            background: var(--color-green-bg);
            color: var(--color-green-text);
        }

        /* Metadatos */
        .meta-table {
            width: 100%;
            margin-bottom: 4px;
            padding-bottom: 4px;
            border-bottom: 1px dashed var(--color-black);
        }

        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .meta-label {
            width: 35%;
            font-weight: 900;
        }

        /* Detalle de consumo */
        .items-section {
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px dashed var(--color-black);
        }

        .items-title {
            margin-bottom: 3px;
            padding-bottom: 2px;
            border-bottom: 1px solid var(--color-black);

            font-size: 11px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .item-row {
            font-size: 10px;
        }

        .item-row td {
            padding: 1px 0;
            vertical-align: top;
        }

        .item-qty {
            width: 12%;
            text-align: center;
        }

        .item-name {
            width: 58%;
        }

        .item-price {
            width: 30%;
            text-align: right;
        }

        /* Totales */
        .total-section {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 2px solid var(--color-black);
            text-align: right;
        }

        .total-line {
            font-size: 10px;
        }

        .total-row {
            margin-top: 4px;
            font-size: 13px;
            font-weight: 900;
        }

        .vuelto {
            margin-top: 3px;
            font-size: 12px;
            font-weight: 900;
            color: var(--color-green-text);
        }

        /* Pie */
        .ticket-footer {
            margin-top: 10px;
            padding-top: 4px;
            border-top: 1px dashed var(--color-black);
            text-align: center;
        }

        .ticket-footer p {
            margin: 0;
            font-size: 9px;
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

<header class="ticket-header">
    <h1 class="hotel-name">
        Hotel Bugambilias
    </h1>

    <h2 class="ticket-title">
        Comprobante de Pago
    </h2>

    <span class="ticket-badge">
        Pago Confirmado
    </span>
</header>

<section>
    <table class="meta-table">
        <tr>
            <td class="meta-label">CUENTA:</td>
            <td><strong>{{ $cuenta->numero_cuenta }}</strong></td>
        </tr>

        <tr>
            <td class="meta-label">FECHA:</td>
            <td>{{ $cuenta->cerrada_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</td>
        </tr>

        <tr>
            <td class="meta-label">CLIENTE:</td>
            <td>{{ $clienteNombre }}</td>
        </tr>

        <tr>
            <td class="meta-label">METODO:</td>
            <td>{{ $metodoPago }}</td>
        </tr>

        @if(filled($pago?->referencia_transaccion))
            <tr>
                <td class="meta-label">REF:</td>
                <td>{{ $pago->referencia_transaccion }}</td>
            </tr>
        @endif
    </table>
</section>

@if($cuenta->detalles->isNotEmpty())
    <section class="items-section">
        <div class="items-title">Detalle de Consumo</div>

        <table style="width: 100%; border-collapse: collapse;">
            @foreach($cuenta->detalles as $detalle)
                <tr class="item-row">
                    <td class="item-qty">
                        {{ (int) $detalle->cantidad }}x
                    </td>

                    <td class="item-name">
                        {{ $detalle->concepto }}
                    </td>

                    <td class="item-price">
                        {{ $simboloMoneda }} {{ number_format((float) $detalle->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </table>
    </section>
@endif

<section class="total-section">
    <div class="total-line">Subtotal: {{ $simboloMoneda }} {{ number_format((float) $cuenta->subtotal, 2) }}</div>
    <div class="total-line">IVA: {{ $simboloMoneda }} {{ number_format((float) $cuenta->impuesto_total, 2) }}</div>
    <div class="total-line">Total: {{ $simboloMoneda }} {{ number_format($totalCuenta, 2) }}</div>
    <div class="total-line">Pagado: {{ $simboloMoneda }} {{ number_format($montoPagado, 2) }}</div>

    @if($vuelto > 0)
        <div class="vuelto">VUELTO: {{ $simboloMoneda }} {{ number_format($vuelto, 2) }}</div>
    @endif

    <div class="total-row">PAGO CONFIRMADO</div>
</section>

<footer class="ticket-footer">
    <p>HOTEL BUGAMBILIAS - Comprobante de Pago</p>
    <p>{{ now()->format('d/m/Y H:i:s') }}</p>
</footer>

</body>
</html>
