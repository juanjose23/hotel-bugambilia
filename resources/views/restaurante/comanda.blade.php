<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda - {{ $pedido->codigo }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 76mm;
            margin: 0 auto;
            padding: 5mm 2mm;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
        }
        .text-center {
            text-align: center;
        }
        .header {
            border-bottom: 1px dashed #000;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 11px;
            margin: 2px 0 0 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 8px;
            font-size: 12px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .info-table td {
            padding: 1px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            padding-bottom: 3px;
        }
        .items-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .qty {
            width: 10%;
            font-weight: bold;
        }
        .name {
            width: 90%;
        }
        .notes {
            font-size: 11px;
            font-style: italic;
            padding-left: 10px;
            display: block;
            margin-top: 2px;
        }
        .footer {
            border-top: 1px dashed #000;
            padding-top: 5px;
            font-size: 11px;
            margin-top: 10px;
        }
        @media print {
            body {
                width: 100%;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
        .no-print-bar {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn {
            background-color: #be185d;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-sec {
            background-color: #4b5563;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="no-print no-print-bar">
        <button class="btn" onclick="window.print()">Imprimir Comanda</button>
        <button class="btn-sec" onclick="window.close()">Cerrar Ventana</button>
    </div>

    <div class="text-center header">
        <h1 class="title">Bugambilias</h1>
        <p class="subtitle">*** COMANDA DE COCINA ***</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Pedido:</strong></td>
            <td style="text-align: right;">{{ $pedido->codigo }}</td>
        </tr>
        <tr>
            <td><strong>Mesa / Área:</strong></td>
            <td style="text-align: right;">{{ $pedido->mesa->nombre ?? 'Llevar / Delivery' }}</td>
        </tr>
        <tr>
            <td><strong>Fecha:</strong></td>
            <td style="text-align: right;">{{ $pedido->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="qty">Cant</th>
                <th class="name">Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->items as $item)
                @if($item->estado !== 'cancelado')
                    <tr>
                        <td class="qty">{{ $item->cantidad }}x</td>
                        <td class="name">
                            <strong>{{ $item->plato->nombre ?? 'Plato General' }}</strong>
                            @if($item->notas)
                                <span class="notes">* NOTA: {{ $item->notas }}</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="text-center footer">
        <p>Generado por Sistema KDS - Hotel Bugambilias</p>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
