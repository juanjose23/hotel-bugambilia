<x-filament-panels::page>
    <div class="space-y-6">
        {{-- ─── Subheader / Encabezado ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Inteligencia de Reservas & Ventas</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Control de ocupación hotelera, facturación por canales de pago y rendimiento de habitaciones.</p>
            </div>
        </div>

        @php
            $reporteKey = $reportData['reporte'] ?? '';
            $reporteConfig = \App\Support\ReporteConfig::getReportes()['reservas'][$reporteKey] ?? null;
            $tituloReporte = $reporteConfig['titulo'] ?? ($reporteKey ?: 'No seleccionado');
            $codigoReporte = $reporteConfig['codigo'] ?? null;
        @endphp

        <x-reportes.layout
            titulo="Configuración del Informe"
            subtitulo="Seleccione el modelo de datos base y ajuste los parámetros requeridos"
            modulo="Módulo de Reservas y Ventas"
            color="indigo"
            icon="heroicon-o-document-chart-bar"
            :codigo="$codigoReporte"
            :tituloReporte="$tituloReporte"
            :reportData="$reportData"
            :tieneExcel="\App\Support\ReporteConfig::tieneFormatoExcel('reservas', $reporteKey)"
        >
            <x-slot:kpis>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- KPI 1 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-gray-950 dark:text-white">{{ $porcentajeOcupacion ?? 0 }}%</span>
                            <x-heroicon-o-home class="w-5 h-5 text-indigo-500" />
                        </div>
                        <span class="text-xs font-semibold text-emerald-500">↗ Ocupación actual</span>
                    </div>

                    {{-- KPI 2 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-gray-950 dark:text-white">{{ $checkinsHoy ?? 0 }}</span>
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-sky-500" />
                        </div>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Check-ins para hoy</span>
                    </div>

                    {{-- KPI 3 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">C$ {{ number_format((float) ($facturacionMes ?? 0), 2) }}</span>
                            <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                        </div>
                        <span class="text-xs font-semibold text-emerald-600/80 dark:text-emerald-400/80">Facturación del mes</span>
                    </div>

                    {{-- KPI 4 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $pagosPendientes ?? 0 }}</span>
                            <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
                        </div>
                        <span class="text-xs font-semibold text-amber-600/80 dark:text-amber-400/80">Saldos pendientes de cobro</span>
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
                        <span><strong>Ocupación:</strong> Muestra porcentaje de ocupación, check-in, check-out y noches reservadas.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5"></span>
                        <span><strong>Ventas:</strong> Desglose por pasarela Stripe, transferencias bancarias y cobros en efectivo.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-sky-500 mt-1.5"></span>
                        <span><strong>Huéspedes:</strong> Directorio de clientes titulares con récord histórico acumulado.</span>
                    </li>
                </ul>
            </div>
        </x-slot:sidebarExtra>
    </x-reportes.layout>
    </div>
</x-filament-panels::page>
