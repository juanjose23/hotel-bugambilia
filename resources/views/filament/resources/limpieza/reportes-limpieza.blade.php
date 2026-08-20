<x-filament-panels::page>
    @php
        $reporteKey = $reportData['reporte'] ?? 'operacion_hotelera';
        $reporteConfig = \App\Support\ReporteConfig::getReportes()['limpieza'][$reporteKey] ?? null;
        $tituloReporte = $reporteConfig['titulo'] ?? 'Reporte de Limpieza y Operación Hotelera';
        $codigoReporte = $reporteConfig['codigo'] ?? 'HTB-LIM-001';
    @endphp

    <x-reportes.layout
        titulo="Configuración del Informe"
        subtitulo="Seleccione el período operativo para analizar limpieza, habitaciones, amenidades y productividad"
        modulo="Módulo de Limpieza & Lavandería"
        icon="heroicon-o-clipboard-document-check"
        :codigo="$codigoReporte"
        :tituloReporte="$tituloReporte"
        :reportData="$reportData"
        :tieneExcel="false"
    >
        <x-slot:kpis>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl font-extrabold text-gray-950 dark:text-white">{{ $resumenOperacion['tiempo_promedio_minutos'] ?? 0 }} min</span>
                        <x-heroicon-o-clock class="w-5 h-5 text-sky-500" />
                    </div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Tiempo promedio</span>
                </div>

                <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $resumenOperacion['pendientes'] ?? 0 }}</span>
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500" />
                    </div>
                    <span class="text-xs font-semibold text-amber-600/80 dark:text-amber-400/80">Pendientes</span>
                </div>

                <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{{ $resumenOperacion['bloqueadas'] ?? 0 }}</span>
                        <x-heroicon-o-no-symbol class="w-5 h-5 text-rose-500" />
                    </div>
                    <span class="text-xs font-semibold text-rose-600/80 dark:text-rose-400/80">Bloqueadas</span>
                </div>

                <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $resumenOperacion['finalizadas'] ?? 0 }}</span>
                        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500" />
                    </div>
                    <span class="text-xs font-semibold text-emerald-600/80 dark:text-emerald-400/80">Finalizadas</span>
                </div>
            </div>
        </x-slot:kpis>

        {{ $this->reportForm }}

        <x-slot:sidebarExtra>
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl">
                <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="p-2.5 bg-[#711C37]/10 text-[#711C37] dark:text-[#e87faa] rounded-xl">
                        <x-heroicon-o-document-chart-bar class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-950 dark:text-white">Contenido del informe</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Indicadores operativos de limpieza</p>
                    </div>
                </div>

                <ul class="space-y-3 text-xs text-gray-600 dark:text-gray-300">
                    <li><strong>Tiempos:</strong> duración promedio y detalle por habitación.</li>
                    <li><strong>Estado operativo:</strong> habitaciones pendientes, en proceso y bloqueadas.</li>
                    <li><strong>Amenities:</strong> consumo registrado por habitación y producto.</li>
                    <li><strong>Productividad:</strong> habitaciones asignadas/finalizadas por colaborador y turno.</li>
                </ul>
            </div>
        </x-slot:sidebarExtra>
    </x-reportes.layout>
</x-filament-panels::page>
