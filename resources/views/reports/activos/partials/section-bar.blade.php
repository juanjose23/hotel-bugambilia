<div style="background:#711C37;color:#fff;padding:8px 12px;border-radius:4px 4px 0 0;font-size:11px;font-weight:bold;text-transform:uppercase;{{ $style ?? '' }}">
    {{ $titulo }}
    @isset($subtitulo)
        <span style="float:right;">{{ $subtitulo }}</span>
    @endisset
</div>
