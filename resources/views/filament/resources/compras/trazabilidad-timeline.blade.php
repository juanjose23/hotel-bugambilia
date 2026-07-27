@php
    $solicitud = $getRecord();
    $items = $solicitud->items;
    $cotizaciones = $solicitud->cotizaciones;
    $ordenes = $solicitud->ordenesCompra;

    // Determine overall progress status
    // Steps: 1 = Solicitada, 2 = Cotizada, 3 = Ordenada (OC), 4 = Recibida
    $currentStep = 1;
    if ($cotizaciones->count() > 0) $currentStep = 2;
    if ($ordenes->count() > 0) {
        $currentStep = 3;
        // Check if any OC is received or partially received
        $hasRecepcion = false;
        foreach($ordenes as $o) {
            if ($o->recepciones->count() > 0) {
                $hasRecepcion = true;
                break;
            }
        }
        if ($hasRecepcion) {
            $currentStep = 4;
        }
    }
@endphp

<div class="space-y-8 p-4">
    <!-- Visual Stepper / Progress Bar -->
    <div class="relative flex items-center justify-between w-full max-w-3xl mx-auto mb-10">
        <!-- Connecting Line -->
        <div class="absolute left-0 right-0 top-1/2 h-1 bg-gray-200 dark:bg-gray-700 -translate-y-1/2 z-0"></div>
        <div class="absolute left-0 top-1/2 h-1 bg-primary-600 dark:bg-primary-500 -translate-y-1/2 z-0 transition-all duration-500" 
             style="width: {{ (($currentStep - 1) / 3) * 100 }}%;"></div>

        <!-- Step 1: Solicitud -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $currentStep >= 1 ? 'bg-primary-600 text-white shadow-lg ring-4 ring-primary-100 dark:ring-primary-900' : 'bg-gray-200 text-gray-500 dark:bg-gray-800' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span class="text-xs font-bold mt-2 text-gray-900 dark:text-white">Solicitud</span>
        </div>

        <!-- Step 2: Cotización -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $currentStep >= 2 ? 'bg-primary-600 text-white shadow-lg ring-4 ring-primary-100 dark:ring-primary-900' : 'bg-gray-200 text-gray-500 dark:bg-gray-800' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <span class="text-xs font-bold mt-2 text-gray-950 dark:text-white">Cotizaciones</span>
        </div>

        <!-- Step 3: Orden de Compra -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $currentStep >= 3 ? 'bg-primary-600 text-white shadow-lg ring-4 ring-primary-100 dark:ring-primary-900' : 'bg-gray-200 text-gray-500 dark:bg-gray-800' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <span class="text-xs font-bold mt-2 text-gray-950 dark:text-white">Orden de Compra</span>
        </div>

        <!-- Step 4: Recepción -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $currentStep >= 4 ? 'bg-success-600 text-white shadow-lg ring-4 ring-success-100 dark:ring-success-950' : 'bg-gray-200 text-gray-500 dark:bg-gray-800' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-xs font-bold mt-2 text-gray-950 dark:text-white">Recibido</span>
        </div>
    </div>

    <!-- Details Sections Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- 1. Solicitud Card -->
        <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                <div class="flex items-center space-x-2">
                    <div class="p-2 bg-primary-50 dark:bg-primary-950/30 rounded-lg text-primary-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="font-bold text-gray-950 dark:text-white">Solicitud {{ $solicitud->codigo }}</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $solicitud->estado->getColor() === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                    {{ $solicitud->estado->getLabel() }}
                </span>
            </div>

            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mb-4">
                <div><span class="font-semibold text-gray-950 dark:text-white">Solicitante:</span> {{ $solicitud->colaborador->persona->nombre_completo ?? '—' }}</div>
                <div><span class="font-semibold text-gray-950 dark:text-white">Departamento:</span> {{ $solicitud->departamentoSolicitante->nombre ?? '—' }}</div>
                <div><span class="font-semibold text-gray-950 dark:text-white">Fecha Creación:</span> {{ $solicitud->fecha_solicitud?->format('d/m/Y') }}</div>
                @if($solicitud->motivo)
                    <div><span class="font-semibold text-gray-950 dark:text-white">Motivo:</span> {{ $solicitud->motivo }}</div>
                @endif
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800 pt-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-950 dark:text-white mb-2">Artículos Solicitados</h4>
                <div class="max-h-48 overflow-y-auto space-y-2 pr-1">
                    @foreach($items as $item)
                        <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-800/40 p-2 rounded-lg text-xs">
                            <div>
                                <span class="font-semibold text-gray-950 dark:text-white">{{ $item->producto->nombre }}</span>
                                @if($item->variante)
                                    <span class="text-gray-900 dark:text-gray-400"> ({{ $item->variante->codigo }})</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-gray-950 dark:text-white font-bold">{{ number_format($item->cantidad_solicitada, 0) }}</span>
                                <span class="text-gray-900">sol.</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 2. Cotizaciones Card -->
        <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                <div class="flex items-center space-x-2">
                    <div class="p-2 bg-blue-50 dark:bg-blue-950/30 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <span class="font-bold text-gray-950 dark:text-white">Cotizaciones Recibidas</span>
                </div>
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $cotizaciones->count() }} ofertas</span>
            </div>

            @if($cotizaciones->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                    <svg class="w-12 h-12 mb-2 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs">No hay cotizaciones para este proceso.</span>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($cotizaciones as $cot)
                        <div class="p-3 rounded-xl border transition-all duration-300 {{ $cot->es_elegida ? 'bg-green-50/50 dark:bg-green-950/10 border-green-200 dark:border-green-900/40 ring-1 ring-green-100 dark:ring-green-950/30' : 'bg-gray-50 dark:bg-gray-800/40 border-gray-100 dark:border-gray-800' }}">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="font-bold text-gray-950 dark:text-white text-xs block">{{ $cot->proveedor->persona->nombre_completo }}</span>
                                    <span class="text-xs text-gray-900">Lead Time: {{ $cot->tiempo_entrega_dias ?? '—' }} días</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-extrabold text-sm text-primary-600 block">${{ number_format($cot->total, 2) }}</span>
                                    @if($cot->es_elegida)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-extrabold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400 uppercase">
                                         Elegida
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 3. Órdenes de Compra Card -->
        <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                <div class="flex items-center space-x-2">
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg text-emerald-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="font-bold text-gray-950 dark:text-white">Órdenes de Compra (OC)</span>
                </div>
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $ordenes->count() }} emitidas</span>
            </div>

            @if($ordenes->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                    <svg class="w-12 h-12 mb-2 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs">No se han emitido Órdenes de Compra.</span>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($ordenes as $o)
                        <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span class="font-bold text-gray-950 dark:text-white text-xs">{{ $o->codigo }}</span>
                                    <span class="text-2xs text-gray-900 block">{{ $o->fecha_orden?->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-extrabold text-sm text-gray-950 dark:text-white block">${{ number_format($o->total, 2) }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-2xs font-semibold {{ $o->estado->getColor() === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/30' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30' }}">
                                        {{ $o->estado->getLabel() }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-xs text-gray-900">
                                <span class="font-semibold text-gray-950 dark:text-white">Proveedor:</span> {{ $o->proveedor->persona->nombre_completo }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 4. Recepción en Bodega Card -->
        <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                <div class="flex items-center space-x-2">
                    <div class="p-2 bg-success-50 dark:bg-success-950/30 rounded-lg text-success-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-bold text-gray-950 dark:text-white">Entradas a Bodega</span>
                </div>
            </div>

            @php
                $recepciones = collect();
                foreach($ordenes as $o) {
                    foreach($o->recepciones as $r) {
                        $recepciones->push($r);
                    }
                }
            @endphp

            @if($recepciones->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                    <svg class="w-12 h-12 mb-2 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs">No se registran recepciones físicas.</span>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recepciones as $r)
                        <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-gray-950 dark:text-white text-xs">{{ $r->codigo }}</span>
                                <span class="text-2xs text-gray-900">{{ $r->fecha_recepcion?->format('d/m/Y') }}</span>
                            </div>
                            <div class="text-2xs text-gray-900 space-y-1">
                                <div><span class="font-semibold text-gray-950 dark:text-white">Recibido por:</span> {{ $r->receptor->persona->nombre_completo ?? $r->receptor->name }}</div>
                                @if($r->factura_referencia)
                                    <div><span class="font-semibold text-gray-950 dark:text-white">Factura Ref:</span> {{ $r->factura_referencia }}</div>
                                @endif
                                <div>
                                    <span class="font-semibold text-gray-950 dark:text-white">Estado Físico:</span>
                                    <span class="font-bold uppercase text-primary-600">{{ $r->estado->getLabel() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
