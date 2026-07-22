@php
    $slotContent = $slot ?? '';
@endphp
<div class="pagina">
    <div class="report-header">
        @include('reports.layout.partials.header', [
            'logo_base64' => $datosHotel['logo_base64'] ?? null,
            'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
        ])
    </div>

    <div class="report-content">
        {!! $slotContent !!}
    </div>

    <div class="report-footer">
        @include('reports.layout.partials.footer', [
            'generadoEn' => $generadoEn ?? now()->format('d/m/Y H:i'),
            'usuario' => $usuario ?? 'Sistema',
        ])
    </div>
</div>
