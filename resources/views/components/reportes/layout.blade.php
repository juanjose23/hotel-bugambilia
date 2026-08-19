@props([
    'titulo' => 'Configuración del Informe',
    'subtitulo' => 'Seleccione el modelo de datos base y ajuste los parámetros requeridos',
    'modulo' => 'Módulo General',
    'codigo' => null,
    'tituloReporte' => null,
    'reportData' => [],
    'tieneExcel' => false,
    'icon' => 'heroicon-o-adjustments-horizontal',
    'onSubmit' => 'descargarReporte',
])

@php
    $fechaInicio = $reportData['fecha_inicio'] ?? $reportData['fecha_desde'] ?? '-';
    $fechaFin = $reportData['fecha_fin'] ?? $reportData['fecha_hasta'] ?? '-';
@endphp

<div x-data x-on:open-new-tab.window="window.open($event.detail.url || $event.detail[0]?.url, '_blank')" class="w-full space-y-6 font-sans">

    {{-- Top Slot: 4 KPI Cards (Obligatorio en todo el proyecto) --}}
    @if(isset($kpis))
        <div>
            {{ $kpis }}
        </div>
    @endif

    {{-- Grid Principal de 2 Columnas --}}
    <div class="reportes-grid">

        {{-- Columna Izquierda: Formulario de Configuración --}}
        <div class="reportes-col-form rounded-3xl border border-gray-200/80 dark:border-gray-800/90 bg-white dark:bg-gray-900 p-6 sm:p-8 shadow-xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-[#711C37]/10 text-[#711C37] dark:text-[#e87faa] ring-1 ring-[#711C37]/20">
                        <x-heroicon-o-adjustments-horizontal class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">{{ $titulo }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subtitulo }}</p>
                    </div>
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Listo para exportar
                </span>
            </div>

            <form wire:submit.prevent="{{ $onSubmit }}" class="space-y-6">
                <div class="space-y-6">
                    {{ $slot }}
                </div>

                {{-- Acciones de Generación (Botones con Color Corporativo Hotel Bugambilias #711C37) --}}
                <div class="pt-6 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <x-heroicon-o-shield-check class="w-4 h-4 text-emerald-500" />
                        <span>Documento oficial con firma y control HTB</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        @if(isset($extraActions))
                            {{ $extraActions }}
                        @elseif($tieneExcel)
                            <button 
                                type="button" 
                                wire:click="descargarExcel"
                                wire:loading.attr="disabled"
                                class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-5 rounded-2xl shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 text-xs"
                            >
                                <x-heroicon-o-table-cells class="w-4 h-4" />
                                <span>Exportar Excel</span>
                            </button>
                        @endif

                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto bg-[#711C37] hover:bg-[#59162b] text-white font-bold py-3.5 px-7 rounded-2xl shadow-lg shadow-[#711C37]/30 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2.5 cursor-pointer disabled:opacity-50 text-xs"
                        >
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4 animate-bounce" wire:loading.remove />
                            <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" wire:loading />
                            <span wire:loading.remove>Generar y Descargar PDF</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Columna Derecha: Analytics & Resumen de Parámetros --}}
        <div class="reportes-col-sidebar space-y-6">

            {{-- Slot Superior Derecha: Chart / Analytics / Top Card --}}
            @if(isset($widgets))
                <div>
                    {{ $widgets }}
                </div>
            @endif

            {{-- Tarjeta de Resumen de Parámetros (Corporativa Hotel Bugambilias) --}}
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white/95 dark:bg-gray-900 p-6 shadow-xl relative overflow-hidden">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-[#711C37]/10 text-[#711C37] dark:text-[#e87faa] rounded-xl">
                            <x-heroicon-o-table-cells class="w-5 h-5" />
                        </div>
                        <h4 class="text-sm font-bold text-gray-950 dark:text-white">Resumen de Parámetros</h4>
                    </div>

                    @if($codigo)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-[#711C37]/10 text-[#711C37] dark:text-[#e87faa] border border-[#711C37]/20">
                            {{ $codigo }}
                        </span>
                    @endif
                </div>

                <div class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-start py-1.5 border-b border-gray-100 dark:border-gray-800/80 gap-3">
                        <span class="text-gray-500 dark:text-gray-400 shrink-0">Reporte:</span>
                        <span class="font-bold text-[#711C37] dark:text-[#e87faa] text-right">
                            {{ $tituloReporte ?? 'Seleccione un reporte' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-800/80">
                        <span class="text-gray-500 dark:text-gray-400">Fecha Inicio:</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-lg">{{ $fechaInicio }}</span>
                    </div>

                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-800/80">
                        <span class="text-gray-500 dark:text-gray-400">Fecha Fin:</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-lg">{{ $fechaFin }}</span>
                    </div>

                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-gray-500 dark:text-gray-400">Formato de Salida:</span>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-[#711C37]/10 text-[#711C37] dark:text-[#e87faa] font-bold border border-[#711C37]/20 text-[10px]">
                                <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                                PDF Document
                            </span>
                            @if($tieneExcel)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/20 text-[10px]">
                                    <x-heroicon-o-table-cells class="w-3.5 h-3.5" />
                                    Excel
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slot Inferior Derecha Extra --}}
            @if(isset($sidebarExtra))
                <div>
                    {{ $sidebarExtra }}
                </div>
            @endif

        </div>

    </div>

</div>
