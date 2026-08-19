<x-filament-panels::page>
    <div class="space-y-6">
        {{-- ─── Subheader / Encabezado ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Inteligencia de Servicios & Tarifas</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Control de tarifas vigentes, histórico de precios por moneda y catálogo de amenidades.</p>
            </div>
        </div>

        @php
            $reporteKey = $reportData['reporte'] ?? '';
            $reporteConfig = \App\Support\ReporteConfig::getReportes()['servicios'][$reporteKey] ?? null;
            $tituloReporte = $reporteConfig['titulo'] ?? ($reporteKey ?: 'No seleccionado');
            $codigoReporte = $reporteConfig['codigo'] ?? null;
        @endphp

        <x-reportes.layout
            titulo="Configuración del Informe"
            subtitulo="Seleccione el modelo de datos base y ajuste los parámetros requeridos"
            modulo="Módulo de Servicios & Tarifas"
            color="indigo"
            icon="heroicon-o-currency-dollar"
            :codigo="$codigoReporte"
            :tituloReporte="$tituloReporte"
            :reportData="$reportData"
            :tieneExcel="\App\Support\ReporteConfig::tieneFormatoExcel('servicios', $reporteKey)"
        >
            <x-slot:kpis>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- KPI 1: Total Servicios --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-gray-950 dark:text-white">{{ $totalServicios ?? 0 }}</span>
                            <x-heroicon-o-sparkles class="w-5 h-5 text-indigo-500" />
                        </div>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Servicios registrados</span>
                    </div>

                    {{-- KPI 2: Precios Vigentes --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $preciosVigentes ?? 0 }}</span>
                            <x-heroicon-o-check-badge class="w-5 h-5 text-emerald-500" />
                        </div>
                        <span class="text-xs font-semibold text-emerald-600/80 dark:text-emerald-400/80">Tarifas vigentes</span>
                    </div>

                    {{-- KPI 3: Monedas --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-sky-600 dark:text-sky-400">{{ $totalMonedas ?? 0 }}</span>
                            <x-heroicon-o-currency-dollar class="w-5 h-5 text-sky-500" />
                        </div>
                        <span class="text-xs font-semibold text-sky-600/80 dark:text-sky-400/80">Monedas configuradas</span>
                    </div>

                    {{-- KPI 4: Categorías --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $totalCategorias ?? 0 }}</span>
                            <x-heroicon-o-tag class="w-5 h-5 text-amber-500" />
                        </div>
                        <span class="text-xs font-semibold text-amber-600/80 dark:text-amber-400/80">Categorías de servicios</span>
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
                            <span><strong>Histórico de Precios:</strong> Detalle de tarifas y precios históricos por categoría, servicio y moneda.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5"></span>
                            <span><strong>Vigencias:</strong> Monitoreo de precios activos y auditoría de cambios de importes.</span>
                        </li>
                    </ul>
                </div>
            </x-slot:sidebarExtra>
        </x-reportes.layout>
    </div>
</x-filament-panels::page>
