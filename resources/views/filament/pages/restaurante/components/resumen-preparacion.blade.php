@php($resumen = $this->resumenPreparacion())
<div data-guia="preparacion-resumen" class="kitchen-review">
    <div class="kitchen-review-source"><span class="kitchen-eyebrow">VAS A RETIRAR</span><h3>{{ number_format($resumen['retiro'], 3, ',', '.') }} {{ $resumen['origen']['unidad'] }}</h3><p>{{ $resumen['origen']['nombre'] }}</p><small>De {{ $resumen['origen']['ubicacion'] }} · disponible: {{ number_format($resumen['origen']['cantidad'], 3, ',', '.') }} {{ $resumen['origen']['unidad'] }}</small></div>
    <h4>Lo que vas a registrar</h4>
    <div class="kitchen-table-wrap"><table class="kitchen-table"><thead><tr><th>Resultado</th><th>Cantidad</th><th>Destino</th></tr></thead><tbody>
        @foreach($resumen['resultados'] as $resultado)<tr><td>{{ $resultado['merma'] ? 'Pérdida registrada' : $resultado['nombre'] }}</td><td>{{ number_format($resultado['cantidad'], 3, ',', '.') }} {{ $resultado['unidad'] }}</td><td>{{ $resultado['merma'] ? 'Registro de desperdicio' : $resultado['bodega'] }}</td></tr>@endforeach
    </tbody></table></div>
    <div class="kitchen-callout">Al guardar, se actualizarán las existencias y quedará el costo real en el historial. Si obtuviste menos, la diferencia se registrará como pérdida de rendimiento.</div>
</div>
