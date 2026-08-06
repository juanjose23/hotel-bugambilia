<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Historial de Transformaciones y Auditoría de Mermas</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Auditoría completa de insumos procesados, porciones generadas y mermas descartadas.</p>
        </div>
    </div>

    @php
        $historial = $this->obtenerHistorialTransformacionesListado();
    @endphp

    @if($historial->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
            <x-heroicon-o-clock class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No hay transformaciones registradas</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">El historial de auditoría aparecerá a medida que porcione materias primas.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($historial as $trans)
                @php
                    $mermasCount = $trans->items->filter(fn ($i) => $i->es_merma)->count();
                    $utilesCount = $trans->items->filter(fn ($i) => ! $i->es_merma)->count();
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                        <div>
                            <span class="font-mono font-bold text-sm text-primary-600 dark:text-primary-400">{{ $trans->codigo }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">• {{ $trans->created_at?->format('d/m/Y h:i A') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-medium">
                                {{ $utilesCount }} útil(es)
                            </span>
                            @if($mermasCount > 0)
                                <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-medium">
                                    {{ $mermasCount }} merma(s)
                                </span>
                            @endif
                            <span class="font-bold text-gray-900 dark:text-white">
                                Total: C$ {{ number_format((float) $trans->costo_total, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="font-bold text-gray-700 dark:text-gray-300 block mb-1">Materia Prima Origen (Retirada)</span>
                            <p class="text-gray-600 dark:text-gray-400">
                                <strong>{{ $trans->productoOrigen?->nombre }}</strong>
                                ({{ $trans->varianteOrigen?->nombre_variante ?: $trans->varianteOrigen?->codigo }})
                                — Cantidad: <span class="font-semibold text-gray-900 dark:text-white">{{ number_format((float) $trans->cantidad_procesada, 2) }}</span>
                            </p>
                            @if($trans->realizadoPor)
                                <p class="text-gray-400 text-[11px] mt-1">Registrado por: {{ $trans->realizadoPor->name }}</p>
                            @endif
                        </div>

                        <div>
                            <span class="font-bold text-gray-700 dark:text-gray-300 block mb-1">Resultados Obtenidos</span>
                            <ul class="space-y-1">
                                @foreach($trans->items as $item)
                                    <li class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                        <span>
                                            @if($item->es_merma)
                                                <span class="text-rose-500 font-semibold">[MERMA]</span> {{ $item->productoDestino?->nombre }}
                                            @else
                                                <span class="text-emerald-500 font-semibold">[ÚTIL]</span> {{ $item->productoDestino?->nombre }} ({{ $item->varianteDestino?->nombre_variante ?: $item->varianteDestino?->codigo }})
                                            @endif
                                        </span>
                                        <span class="font-medium">
                                            {{ number_format((float) $item->cantidad, 2) }}
                                            @if(! $item->es_merma)
                                                (C$ {{ number_format((float) $item->costo_asignado, 2) }})
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
