<x-filament-panels::page>
    <div class="space-y-6">
        {{-- ─── Subheader / Encabezado ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Inteligencia de Activos Fijos</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Inventario general, hojas de habitación, control de garantías y programación de mantenimientos.</p>
            </div>
        </div>

        @php
            $reporteKey = $reportData['reporte'] ?? '';
            $reporteConfig = \App\Support\ReporteConfig::getReportes()['activos'][$reporteKey] ?? null;
            $tituloReporte = $reporteConfig['titulo'] ?? ($reporteKey ?: 'No seleccionado');
            $codigoReporte = $reporteConfig['codigo'] ?? null;
        @endphp

        <x-reportes.layout
            titulo="Configuración del Informe"
            subtitulo="Seleccione el modelo de datos base y ajuste los parámetros requeridos"
            modulo="Módulo de Activos Fijos"
            color="indigo"
            icon="heroicon-o-document-chart-bar"
            :codigo="$codigoReporte"
            :tituloReporte="$tituloReporte"
            :reportData="$reportData"
            :tieneExcel="\App\Support\ReporteConfig::tieneFormatoExcel('activos', $reporteKey)"
        >
            <x-slot:kpis>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- KPI 1: Total activos --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-gray-950 dark:text-white">{{ $totalActivos ?? 0 }}</span>
                            <x-heroicon-o-computer-desktop class="w-5 h-5 text-indigo-500" />
                        </div>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Activos registrados</span>
                    </div>

                    {{-- KPI 2: Pendientes revisión --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $activosPendientes ?? 0 }}</span>
                            <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
                        </div>
                        <span class="text-xs font-semibold text-amber-600/80 dark:text-amber-400/80">En proceso / Programados</span>
                    </div>

                    {{-- KPI 3: Valor neto --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">C$ {{ number_format((float) ($valorTotalActivos ?? 0), 2) }}</span>
                            <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                        </div>
                        <span class="text-xs font-semibold text-emerald-600/80 dark:text-emerald-400/80">Valor de adquisición</span>
                    </div>

                    {{-- KPI 4: Mantenimientos vencidos --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{{ $mantenimientosVencidos ?? 0 }}</span>
                            <x-heroicon-o-wrench-screwdriver class="w-5 h-5 text-rose-500" />
                        </div>
                        <span class="text-xs font-semibold text-rose-600/80 dark:text-rose-400/80">Mantenimientos vencidos</span>
                    </div>
                </div>
            </x-slot:kpis>

        {{ $this->reportForm }}

        <x-slot:sidebarExtra>
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl">
                <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <x-heroicon-o-academic-cap class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-950 dark:text-white">Descripción de Informes</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Alcance de los reportes del módulo</p>
                    </div>
                </div>

                <ul class="space-y-3 text-xs text-gray-600 dark:text-gray-300">
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5"></span>
                        <span><strong>Hoja de Habitación:</strong> Detalle completo de activos asignados por habitación en un rango de fechas.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5"></span>
                        <span><strong>Inventario:</strong> Listado general de activos con estado, ubicación y valor registrado.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500 mt-1.5"></span>
                        <span><strong>Mantenimiento:</strong> Historial de servicios, vencimientos y alertas de mantenimiento preventivo.</span>
                    </li>
                </ul>
            </div>
        </x-slot:sidebarExtra>
    </x-reportes.layout>
    </div>
</x-filament-panels::page>
