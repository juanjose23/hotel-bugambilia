<div style="display:flex;gap:12px;margin-bottom:12px;padding-left:12px;border-left:3px solid {{ $evento['color'] ?? '#6b7280' }};">
    <div style="flex-shrink:0;width:80px;">
        <div style="font-size:9px;font-weight:bold;color:#555;">{{ $evento['fecha'] ?? '—' }}</div>
    </div>
    <div style="flex:1;">
        <div style="font-size:10px;font-weight:bold;color:{{ $evento['color'] ?? '#6b7280' }};text-transform:uppercase;">
            {{ $evento['tipo'] ?? '—' }}
        </div>
        <div style="font-size:9px;color:#333;margin-top:2px;">
            {{ $evento['detalle'] ?? '—' }}
        </div>
        @if($evento['responsable'] ?? null)
            <div style="font-size:8px;color:#888;margin-top:2px;">
                Responsable: {{ $evento['responsable'] }}
            </div>
        @endif
    </div>
</div>
