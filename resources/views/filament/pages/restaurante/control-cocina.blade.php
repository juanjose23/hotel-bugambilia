<x-filament-panels::page>
    <div class="kitchen-shell" wire:poll.30s>
        @if(config('cocina.demo'))
            <div class="kitchen-demo"><span></span> Demostración · los datos que ves son de ejemplo</div>
        @endif
        <section class="kitchen-hero">
            <div><p class="kitchen-eyebrow">OPERACIÓN DEL DÍA · {{ now()->format('d/m/Y') }}</p><h2>Tu cocina, bajo control.</h2><p>Revisa lo pendiente y elige qué necesitas hacer. Cada preparación y movimiento queda registrado.</p></div>
            <div class="kitchen-hero-note"><x-filament::icon icon="heroicon-o-check-badge" class="h-6 w-6" /><span>Del insumo al servicio.<br><strong>Sin perder el control.</strong></span></div>
        </section>
        <section data-guia="cocina-indicadores" class="kitchen-metrics" aria-label="Resumen de cocina">
            @foreach([
                ['valor' => $resumen['pedidos'], 'label' => 'Comandas activas', 'nota' => $resumen['bloqueados'].' partidas con faltantes', 'icono' => 'heroicon-o-fire'],
                ['valor' => $resumen['borradores'], 'label' => 'Preparaciones sin confirmar', 'nota' => 'Todavía no descuentan insumos', 'icono' => 'heroicon-o-beaker'],
                ['valor' => $resumen['buffets'], 'label' => 'Buffets abiertos hoy', 'nota' => 'Controla reposiciones y cierre', 'icono' => 'heroicon-o-cake'],
                ['valor' => $resumen['reponer'], 'label' => 'Insumos por reponer', 'nota' => $resumen['por_vencer'].' lotes próximos a vencer', 'icono' => 'heroicon-o-archive-box'],
            ] as $metrica)
                <article class="kitchen-metric"><div class="kitchen-metric-top"><span>{{ $metrica['label'] }}</span><x-filament::icon :icon="$metrica['icono']" class="h-5 w-5" /></div><strong>{{ $metrica['valor'] }}</strong><p>{{ $metrica['nota'] }}</p></article>
            @endforeach
        </section>
        <section><div class="kitchen-section-title"><div><h3>¿Qué necesitas hacer?</h3><p>Accesos directos para el trabajo del encargado.</p></div><span>Operación diaria</span></div>
            <div data-guia="cocina-tareas" class="kitchen-task-grid">
                @if(\App\Filament\Pages\Restaurante\MateriaPrimaCocina::canAccess())
                    <a href="{{ \App\Filament\Pages\Restaurante\MateriaPrimaCocina::getUrl() }}" class="kitchen-task" wire:navigate><span class="kitchen-task-icon"><x-filament::icon icon="heroicon-o-scissors" class="h-6 w-6" /></span><h4>Preparar o porcionar</h4><p>Retira un insumo y registra los cortes, porciones o bebidas que obtuviste.</p><span class="kitchen-task-link">Comenzar preparación <span aria-hidden="true">→</span></span></a>
                @endif
                @if(\App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource::canViewAny())
                    <a href="{{ \App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource::getUrl() }}" class="kitchen-task" wire:navigate><span class="kitchen-task-icon kitchen-task-icon-amber"><x-filament::icon icon="heroicon-o-beaker" class="h-6 w-6" /></span><h4>Elaborar una receta</h4><p>Planifica la preparación, revisa sus ingredientes y confirma el resultado real.</p><span class="kitchen-task-link">Ver preparaciones <span aria-hidden="true">→</span></span></a>
                @endif
                @if(\App\Filament\Pages\Restaurante\BuffetsCocina::canAccess())
                    <a href="{{ \App\Filament\Pages\Restaurante\BuffetsCocina::getUrl() }}" class="kitchen-task" wire:navigate><span class="kitchen-task-icon kitchen-task-icon-green"><x-filament::icon icon="heroicon-o-cake" class="h-6 w-6" /></span><h4>Atender buffet o evento</h4><p>Envía alimentos, repón bandejas y registra lo servido, sobrante o perdido.</p><span class="kitchen-task-link">Controlar servicio <span aria-hidden="true">→</span></span></a>
                @endif
            </div>
        </section>
        <section data-guia="cocina-alertas" class="kitchen-panel"><div class="kitchen-section-title"><div><h3>Antes de seguir, revisa esto</h3><p>Existencias próximas a vencer y reposiciones pendientes. Cada cantidad mantiene su unidad.</p></div><span>{{ count($resumen['alertas']) }} avisos</span></div>
            @if($resumen['vencidos'] > 0)<div class="kitchen-callout kitchen-callout-danger">Hay {{ $resumen['vencidos'] }} lotes vencidos. Sepáralos y registra su disposición; no están disponibles para preparar.</div>@endif
            <div class="kitchen-table-wrap"><table class="kitchen-table"><thead><tr><th>Insumo</th><th>Bodega</th><th>Disponible / registrado</th><th>Qué revisar</th></tr></thead><tbody>
                @forelse($resumen['alertas'] as $alerta)<tr><td data-label="Insumo"><strong>{{ $alerta['producto'] }}</strong></td><td data-label="Bodega">{{ $alerta['ubicacion'] }}</td><td data-label="Cantidad">{{ number_format($alerta['cantidad'], 3, ',', '.') }} {{ $alerta['unidad'] }}</td><td data-label="Qué revisar"><span class="kitchen-status kitchen-status-{{ $alerta['nivel'] }}">{{ $alerta['mensaje'] }}</span></td></tr>
                @empty<tr><td colspan="4"><div class="kitchen-empty"><x-filament::icon icon="heroicon-o-check-circle" class="h-7 w-7" /><strong>Sin avisos por ahora</strong><p>Las alertas aparecerán cuando haya una reposición o un vencimiento por revisar.</p></div></td></tr>@endforelse
            </tbody></table></div>
        </section>
        <div class="kitchen-flow"><span>El recorrido de tus insumos</span><p>Recibir <b>→</b> Preparar <b>→</b> Servir <b>→</b> Conciliar</p><small>Los sobrantes y las pérdidas se registran por separado.</small></div>
    </div>
</x-filament-panels::page>
