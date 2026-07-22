<span style="font-weight:bold;color:#711C37;{{ $style ?? '' }}">
    {{ $monedaSimbolo ?? '$' }}{{ number_format((float) ($monto ?? 0), 2) }}
</span>
