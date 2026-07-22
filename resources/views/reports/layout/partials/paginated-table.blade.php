@foreach($paginas as $i => $items)
    <div class="pagina">
        <div class="report-header">
            @include('reports.layout.partials.header', [
                'logo_base64' => $datosHotel['logo_base64'] ?? null,
                'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            ])
        </div>
        <div class="report-content">
            @if(isset($fechaInicio) && isset($fechaFin))
                <div style="margin-bottom:16px;background:#f8fafc;padding:12px 15px;border-radius:4px;border:1px solid #e2e8f0;display:flex;gap:24px;">
                    <div>
                        <span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">Período:</span>&nbsp;
                        <span style="font-size:11px;font-weight:bold;">{{ $fechaInicio }} — {{ $fechaFin }}</span>
                    </div>
                    @if(isset($extraFilters) && is_array($extraFilters))
                        @foreach($extraFilters as $label => $val)
                            @if($val)
                                <div>
                                    <span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">{{ $label }}:</span>&nbsp;
                                    <span style="font-size:11px;font-weight:bold;">{{ $val }}</span>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            @endif

            @include($tableView, ['items' => $items])

            @if(isset($alertMessage) && $loop->last)
                <div style="margin-top:12px;font-size:9px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;padding:8px;border-radius:4px;">
                    {{ $alertMessage }}
                </div>
            @endif
        </div>
        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => now()->format('d/m/Y H:i'),
                'usuario' => $usuario ?? 'Sistema',
            ])
        </div>
    </div>
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
