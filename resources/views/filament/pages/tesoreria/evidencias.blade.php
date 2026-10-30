<div class="kitchen-shell"><p>Compara la imagen con el movimiento real del banco antes de aprobar.</p>
@foreach($evidencias as $e)<div class="kitchen-panel"><p><a class="fi-link" href="{{ $e['url'] }}" target="_blank" rel="noopener">Ver evidencia {{ $e['id'] }} · {{ $e['fecha'] }}</a></p>@if($e['es_imagen'])<img src="{{ $e['url'] }}" alt="Comprobante de transferencia {{ $e['id'] }}" loading="lazy" style="display:block;width:100%;max-height:65vh;object-fit:contain"/>@endif</div>@endforeach
@foreach($decisiones as $d)<div class="kitchen-callout"><strong>{{ $d['decision'] }}</strong><p>{{ $d['motivo'] }}</p></div>@endforeach
</div>
