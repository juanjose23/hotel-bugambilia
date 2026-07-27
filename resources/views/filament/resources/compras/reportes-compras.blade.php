<x-filament-panels::page>
    <div x-data="{ activeTab: 'trazabilidad' }" x-on:open-new-tab.window="window.open($event.detail.url, '_blank')" class="space-y-6">

        {{-- ─── Navegación de Pestañas (Modern Tabs) ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-3 gap-4 dark:border-gray-700">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Centro de Inteligencia de Compras</h1>
                <p class="text-xs text-gray-500 mt-0.5">Control de trazabilidad de solicitudes, análisis de cotizaciones y descarga de reportes.</p>
            </div>

            <div class="flex flex-wrap gap-1.5 bg-gray-100 p-1 rounded-xl dark:bg-gray-900 shadow-inner">
                <button
                    @click="activeTab = 'trazabilidad'"
                    :class="activeTab === 'trazabilidad' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/30'"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <x-heroicon-s-arrow-path class="h-4 w-4" />
                    <span>Trazabilidad de Compra</span>
                </button>
                <button
                    @click="activeTab = 'reports'"
                    :class="activeTab === 'reports' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/30'"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <x-heroicon-s-arrow-down-tray class="h-4 w-4" />
                    <span>Descargar Reportes</span>
                </button>
            </div>
        </div>

        {{-- ─── PESTAÑA 1: TRAZABILIDAD DE COMPRAS ───────────────────────────── --}}
        <div x-show="activeTab === 'trazabilidad'" x-transition class="space-y-6">
            <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 max-w-3xl mx-auto">
                <div class="flex items-center gap-4 mb-6 border-b border-gray-50 dark:border-gray-850 pb-4">
                    <div class="p-3 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-xl shadow-sm ring-1 ring-primary-100/10">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-950 dark:text-white">Buscador de Trazabilidad Operativa</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ingrese el código de cualquier documento (Solicitud, Cotización u Orden de Compra) para reconstruir el flujo completo.</p>
                    </div>
                </div>

                <form wire:submit.prevent="buscarTrazabilidad" class="space-y-5">
                    {{ $this->searchForm }}

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800 mt-6">
                        <x-filament::button type="submit" color="primary" icon="heroicon-o-magnifying-glass">
                            Buscar y Reconstruir
                        </x-filament::button>
                    </div>
                </form>
            </div>

            {{-- Detalle de Trazabilidad Visual --}}
            @if($solicitudSeleccionada)
                <div class="rounded-2xl border border-primary-200 bg-white p-6 shadow-sm dark:border-primary-900/40 dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 rounded-lg">
                                <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin-slow" />
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-950 dark:text-white">Flujo del Proceso: {{ $solicitudSeleccionada->codigo }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Diagrama interactivo del estado actual del proceso de compra.</p>
                            </div>
                        </div>
                        <x-filament::button size="sm" color="gray" icon="heroicon-m-x-mark" wire:click="clearSelected">
                            Cerrar Vista
                        </x-filament::button>
                    </div>

                    {{-- Flujo Visual Horizontal / Timeline --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 relative">
                        {{-- 1. SOLICITUD --}}
                        <div class="flex flex-col items-center text-center p-4 rounded-xl border {{ $solicitudSeleccionada->estado ? 'bg-primary-50/50 border-primary-200 dark:bg-primary-950/20 dark:border-primary-800/40' : 'bg-gray-50 border-gray-200 dark:bg-gray-900' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary-600 text-white font-bold mb-3 shadow-md">
                                1
                            </div>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">Solicitud</span>
                            <span class="text-xs font-mono font-semibold text-primary-600 dark:text-primary-400 mt-1">{{ $solicitudSeleccionada->codigo }}</span>
                            <span class="text-[10px] text-gray-500 mt-1">{{ $solicitudSeleccionada->fecha_solicitud?->format('d/m/Y') }}</span>
                            @if($solicitudSeleccionada->estado)
                                <span class="mt-2 text-xs font-semibold px-2 py-0.5 rounded-full {{ $solicitudSeleccionada->estado->getColorClass() ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $solicitudSeleccionada->estado->getLabel() ?? $solicitudSeleccionada->estado->name }}
                                </span>
                            @endif
                        </div>

                        {{-- 2. COTIZACIONES --}}
                        @php
                            $cotizaciones = $solicitudSeleccionada->cotizaciones;
                            $hasCotizaciones = $cotizaciones->count() > 0;
                        @endphp
                        <div class="flex flex-col items-center text-center p-4 rounded-xl border {{ $hasCotizaciones ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/40' : 'bg-gray-50/50 border-gray-200 dark:bg-gray-900' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $hasCotizaciones ? 'bg-emerald-600' : 'bg-gray-300' }} text-white font-bold mb-3 shadow-md">
                                2
                            </div>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">Cotizaciones</span>
                            <span class="text-xs text-gray-500 mt-1">{{ $cotizaciones->count() }} recibidas</span>
                            @if($hasCotizaciones)
                                <div class="mt-2 flex flex-col gap-1 w-full text-[10px] text-left border-t border-emerald-100 pt-2 dark:border-emerald-950/30">
                                    @foreach($cotizaciones as $cot)
                                        <div class="flex justify-between items-center">
                                            <span class="truncate max-w-[80px] font-medium">{{ $cot->proveedor?->nombre_comercial }}</span>
                                            <span class="font-bold">{{ number_format($cot->total, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="mt-2 text-[10px] text-gray-400">Ninguna cotización cargada</span>
                            @endif
                        </div>

                        {{-- 3. CUADRO COMPARATIVO --}}
                        @php
                            $hasComparativa = $hasCotizaciones; // Si tiene cotizaciones, tiene cuadro comparativo generado
                        @endphp
                        <div class="flex flex-col items-center text-center p-4 rounded-xl border {{ $hasComparativa ? 'bg-cyan-50/50 border-cyan-200 dark:bg-cyan-950/20 dark:border-cyan-900/40' : 'bg-gray-50/50 border-gray-200 dark:bg-gray-900' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $hasComparativa ? 'bg-cyan-600' : 'bg-gray-300' }} text-white font-bold mb-3 shadow-md">
                                3
                            </div>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">Comparativa</span>
                            <span class="text-xs text-gray-500 mt-1">{{ $hasComparativa ? 'Generado' : 'Pendiente' }}</span>
                            @if($hasComparativa)
                                <a href="{{ route('reporte.comparativa', ['solicitud' => $solicitudSeleccionada->id]) }}" target="_blank" class="mt-3 text-[10px] font-bold text-cyan-600 hover:underline dark:text-cyan-400 flex items-center gap-1">
                                    <x-heroicon-m-document-arrow-down class="w-3.5 h-3.5" />
                                    <span>Ver PDF</span>
                                </a>
                            @endif
                        </div>

                        {{-- 4. ORDEN DE COMPRA --}}
                        @php
                            $ordenes = $solicitudSeleccionada->ordenesCompra;
                            $hasOrden = $ordenes->count() > 0;
                        @endphp
                        <div class="flex flex-col items-center text-center p-4 rounded-xl border {{ $hasOrden ? 'bg-blue-50/50 border-blue-200 dark:bg-blue-950/20 dark:border-blue-900/40' : 'bg-gray-50/50 border-gray-200 dark:bg-gray-900' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $hasOrden ? 'bg-blue-600' : 'bg-gray-300' }} text-white font-bold mb-3 shadow-md">
                                4
                            </div>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">Orden Compra</span>
                            @if($hasOrden)
                                @foreach($ordenes as $oc)
                                    <div class="mt-2 flex flex-col items-center">
                                        <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400">{{ $oc->codigo }}</span>
                                        <span class="text-[10px] text-gray-500 mt-0.5">{{ $oc->proveedor?->nombre_comercial }}</span>
                                        <span class="text-[10px] font-bold mt-0.5">{{ number_format((float)$oc->total, 2) }}</span>
                                        @if($oc->estado)
                                            <span class="mt-1.5 text-[9px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                {{ $oc->estado->getLabel() ?? $oc->estado->name }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <span class="mt-2 text-[10px] text-gray-400">Aún no emitida</span>
                            @endif
                        </div>

                        {{-- 5. RECEPCION / CIERRE --}}
                        @php
                            $recepcionesCount = 0;
                            $devolucionesCount = 0;
                            if($hasOrden) {
                                foreach($ordenes as $oc) {
                                    $recepcionesCount += $oc->recepciones()->count();
                                    $devolucionesCount += \App\Repository\Models\Compras\DevolucionCompra::where('orden_compra_id', $oc->id)->count();
                                }
                            }
                            $hasRecepcion = $recepcionesCount > 0;
                        @endphp
                        <div class="flex flex-col items-center text-center p-4 rounded-xl border {{ $hasRecepcion ? 'bg-violet-50/50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900/40' : 'bg-gray-50/50 border-gray-200 dark:bg-gray-900' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $hasRecepcion ? 'bg-violet-600' : 'bg-gray-300' }} text-white font-bold mb-3 shadow-md">
                                5
                            </div>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">Recepción</span>
                            <span class="text-xs text-gray-500 mt-1">{{ $recepcionesCount }} entregas</span>
                            @if($devolucionesCount > 0)
                                <span class="mt-1.5 text-[9px] px-1.5 py-0.5 rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 font-semibold">
                                    {{ $devolucionesCount }} Devoluciones
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Tabla de Ítems e Historial Detallado --}}
                    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Ítems de la Solicitud --}}
                        <div class="rounded-xl border border-gray-150 p-4 dark:border-gray-700">
                            <h5 class="text-sm font-bold text-gray-950 dark:text-white mb-3 flex items-center gap-1.5">
                                <x-heroicon-o-list-bullet class="w-4 h-4 text-primary-500" />
                                <span>Detalle de Artículos Solicitados</span>
                            </h5>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                                    <thead class="bg-gray-50 uppercase text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                                        <tr>
                                            <th class="px-3 py-2">Producto</th>
                                            <th class="px-3 py-2 text-right">Cant. Solicitada</th>
                                            <th class="px-3 py-2 text-right">Precio Ref.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                        @foreach($solicitudSeleccionada->items as $item)
                                            <tr>
                                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $item->producto?->nombre ?? 'N/A' }}</td>
                                                <td class="px-3 py-2 text-right font-semibold">{{ number_format($item->cantidad, 2) }}</td>
                                                <td class="px-3 py-2 text-right">{{ number_format($item->precio_referencia, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Bitácora Operativa de Cambios de Estado --}}
                        <div class="rounded-xl border border-gray-150 p-4 dark:border-gray-700">
                            <h5 class="text-sm font-bold text-gray-950 dark:text-white mb-3 flex items-center gap-1.5">
                                <x-heroicon-o-clock class="w-4 h-4 text-primary-500" />
                                <span>Bitácora de Estados y Auditoría</span>
                            </h5>
                            <div class="relative pl-6 border-l border-gray-200 dark:border-gray-700 space-y-4">
                                @forelse($solicitudSeleccionada->historialEstados ?? [] as $historial)
                                    <div class="relative">
                                        <span class="absolute -left-[31px] top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary-100 ring-4 ring-white dark:bg-primary-950 dark:ring-gray-800">
                                            <span class="h-2 w-2 rounded-full bg-primary-600"></span>
                                        </span>
                                        <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                            Estado cambiado a
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-400">
                                                {{ $historial->estado }}
                                            </span>
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                            Por: {{ $historial->user?->name ?? 'Sistema' }} | {{ $historial->created_at?->format('d/m/Y H:i') }}
                                        </p>
                                        @if($historial->observaciones)
                                            <p class="text-[10px] text-gray-500 italic mt-1 bg-gray-50 p-1.5 rounded border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                                                "{{ $historial->observaciones }}"
                                            </p>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-xs text-gray-400 py-4 italic">
                                        No hay registros históricos de estado para esta solicitud.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tabla de Solicitudes --}}
            <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center gap-2 mb-4">
                    <x-heroicon-o-table-cells class="w-5 h-5 text-primary-500" />
                    <h4 class="text-base font-bold text-gray-950 dark:text-white">Listado Histórico de Compras</h4>
                </div>
                {{ $this->table }}
            </div>
        </div>

        {{-- ─── PESTAÑA 2: CENTRO DE REPORTES (DESCARGAS) ────────────────────── --}}
        <div x-show="activeTab === 'reports'" x-transition class="space-y-6" style="display: none;">
            <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 max-w-3xl mx-auto">
                <div class="flex items-center gap-4 mb-6 border-b border-gray-50 dark:border-gray-850 pb-4">
                    <div class="p-3 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-xl shadow-sm ring-1 ring-primary-100/10">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-950 dark:text-white">Centro de Reportes y Análisis de Compras</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Genera y descarga informes de solicitudes, órdenes de compra y analíticas del hotel en formato PDF.</p>
                    </div>
                </div>

                <form wire:submit.prevent="descargarReporte" class="space-y-5 flex-grow flex flex-col justify-between">
                    <div class="space-y-4">
                        {{ $this->reportForm }}
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800 mt-6">
                        <x-filament::button type="submit" color="primary" icon="heroicon-o-document-arrow-down" class="hover:scale-[1.02] transition-transform duration-200">
                            Generar y Descargar PDF
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-filament-panels::page>
