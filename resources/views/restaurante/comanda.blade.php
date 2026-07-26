<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda #{{ $pedido->codigo }} - {{ $tipo?->getLabel() ?? 'COMANDA' }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px 8px;
            width: 80mm;
            box-sizing: border-box;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 2px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 13px;
            margin: 2px 0;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-tipo {
            display: inline-block;
            border: 1px solid #000;
            padding: 2px 6px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 3px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            font-size: 12px;
        }
        .meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            width: 38%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding-bottom: 3px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .items-table td {
            padding: 5px 0;
            border-bottom: 1px dotted #aaa;
            vertical-align: top;
        }
        .qty {
            font-weight: bold;
            font-size: 14px;
            width: 32px;
            text-align: center;
        }
        .item-name {
            font-weight: bold;
            font-size: 13px;
        }
        .item-note {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
            color: #111;
        }
        .total-section {
            border-top: 2px solid #000;
            padding-top: 6px;
            margin-top: 8px;
            text-align: right;
        }
        .total-row {
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }
        .no-print {
            margin-bottom: 12px;
            text-align: center;
        }
        .btn-print {
            background: #0f172a;
            color: #ffffff;
            border: 1px solid #334155;
            padding: 8px 18px;
            border-radius: 6px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            items-center: center;
            gap: 6px;
        }
        .btn-print:hover {
            background: #1e293b;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir Comanda POS
        </button>
    </div>

    <div class="header">
        <h1>HOTEL BUGAMBILIAS</h1>
        <h2>[ {{ $area ? 'ÁREA: ' . strtoupper($area->getLabel()) : 'COMANDA RESTAURANTE' }} ]</h2>
        <div class="badge-tipo">
            {{ $tipo?->getLabel() ?? 'NUEVO PEDIDO' }} (COP. #{{ $pedido->consecutivo_comanda ?? 1 }})
        </div>
    </div>

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
            <td>{{ now()->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="meta-label">CLIENTE:</td>
            <td>{{ $pedido->cliente?->nombre_completo ?? ('Cliente ' . ($pedido->mesa->nombre ?? 'Mostrador')) }}</td>
        </tr>
        @if($pedido->mesero?->persona)
        <tr>
            <td class="meta-label">MESERO:</td>
            <td>{{ $pedido->mesero->persona->nombre_completo }}</td>
        </tr>
        @endif
        @if($pedido->cuenta)
        <tr>
            <td class="meta-label">HABITACIÓN:</td>
            <td>{{ $pedido->cuenta->estancia?->habitacion?->numero ?? 'Cuenta Habitación' }}</td>
        </tr>
        @endif
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">CANT</th>
                <th>DESCRIPCIÓN / DETALLE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @if($item->estado?->value !== 'cancelado')
                    <tr>
                        <td class="qty">x{{ (int) $item->cantidad }}</td>
                        <td>
                            <div class="item-name">{{ $item->plato->nombre ?? 'Platillo' }}</div>
                            @if($item->observaciones)
                                <div class="item-note">-> OBS: {{ $item->observaciones }}</div>
                            @elseif($item->notas)
                                <div class="item-note">-> NOTA: {{ $item->notas }}</div>
                            @endif
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="2" style="text-align: center; padding: 8px;">Sin ítems para esta área.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($pedido->notas)
        <div style="border: 1px solid #000; padding: 5px; margin-top: 8px; font-size: 11px;">
            <strong>OBSERVACIONES GENERALES:</strong><br>
            {{ $pedido->notas }}
        </div>
    @endif

    <div class="total-section">
        <div class="total-row">
            TOTAL PEDIDO: C$ {{ number_format((float)$pedido->total, 2) }}
        </div>
    </div>

    <div class="footer">
        <p>HOTEL BUGAMBILIAS POS</p>
        <p>Impresión Automática por Área</p>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
