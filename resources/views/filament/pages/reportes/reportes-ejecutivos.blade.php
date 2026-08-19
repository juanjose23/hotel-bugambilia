<x-filament-panels::page>
    <div class="space-y-6">
        {{-- ─── Subheader / Encabezado ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Inteligencia Financiera & Ejecutivo</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Consolidado de ingresos, recaudación fiscal, cuentas por cobrar y estado de resultados.</p>
            </div>
        </div>

        @php
            $reporteKey = $reportData['reporte'] ?? '';
            $reporteConfig = \App\Support\ReporteConfig::getReportes()['financiero'][$reporteKey] ?? null;
            $tituloReporte = $reporteConfig['titulo'] ?? ($reporteKey ?: 'No seleccionado');
            $codigoReporte = $reporteConfig['codigo'] ?? null;
        @endphp

        <x-reportes.layout
            titulo="Configuración del Informe"
            subtitulo="Seleccione el modelo de datos base y ajuste los parámetros requeridos"
            modulo="Módulo Financiero y Ejecutivo"
            color="indigo"
            icon="heroicon-o-chart-pie"
            :codigo="$codigoReporte"
            :tituloReporte="$tituloReporte"
            :reportData="$reportData"
            :tieneExcel="\App\Support\ReporteConfig::tieneFormatoExcel('financiero', $reporteKey)"
        >
            <x-slot:kpis>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- KPI 1 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-gray-950 dark:text-white">C$ {{ number_format($totalIngresosReservas, 2) }}</span>
                            <x-heroicon-o-currency-dollar class="w-5 h-5 text-emerald-500" />
                        </div>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total acumulado ({{ $cantidadReservas }} reservas)</span>
                    </div>

                    {{-- KPI 2 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-teal-600 dark:text-teal-400">C$ {{ number_format($totalRecaudado, 2) }}</span>
                            <x-heroicon-o-banknotes class="w-5 h-5 text-teal-500" />
                        </div>
                        <span class="text-xs font-semibold text-teal-600/80 dark:text-teal-400/80">Recaudación neta Stripe/Efectivo</span>
                    </div>

                    {{-- KPI 3 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">C$ {{ number_format($totalCuentasPorCobrar, 2) }}</span>
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-rose-500" />
                        </div>
                        <span class="text-xs font-semibold text-rose-600/80 dark:text-rose-400/80">Cuentas pendientes por cobrar</span>
                    </div>

                    {{-- KPI 4 --}}
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">C$ {{ number_format($totalFacturadoFiscal, 2) }}</span>
                            <x-heroicon-o-document-check class="w-5 h-5 text-indigo-500" />
                        </div>
                        <span class="text-xs font-semibold text-indigo-600/80 dark:text-indigo-400/80">Facturación fiscal emitida</span>
                    </div>
                </div>
            </x-slot:kpis>

            {{ $this->reportForm }}

            <x-slot:sidebarExtra>
                <div class="space-y-6">
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl">
                        <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                            <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                                <x-heroicon-o-academic-cap class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Descripción de Informes</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Alcance de los reportes financieros</p>
                            </div>
                        </div>

                        <ul class="space-y-3 text-xs text-gray-600 dark:text-gray-300">
                            <li class="flex items-start gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5"></span>
                                <span><strong>Cuentas por Cobrar:</strong> Saldos pendientes de liquidación y seguimiento de crédito.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5"></span>
                                <span><strong>Facturación Fiscal:</strong> Reporte fiscal de comprobantes emitidos e impuestos.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-sky-500 mt-1.5"></span>
                                <span><strong>Resumen Ejecutivo:</strong> Estado consolidado de ingresos brutos y recaudación.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl">
                        <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                            <div class="p-2.5 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                                <x-heroicon-o-light-bulb class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Acciones Sugeridas para Dirección</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Indicadores clave de rentabilidad</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @if($totalCuentasPorCobrar > 0)
                                <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
                                    <div class="flex items-center gap-2 text-rose-700 dark:text-rose-300 font-bold text-xs">
                                        <x-heroicon-m-exclamation-triangle class="w-4 h-4 shrink-0" />
                                        <span>Gestión de Cobranza Pendiente</span>
                                    </div>
                                    <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 leading-relaxed">
                                        Hay <strong>C$ {{ number_format($totalCuentasPorCobrar, 2) }}</strong> pendientes por saldar. Se recomienda enviar recordatorios a clientes antes de su fecha de corte.
                                    </p>
                                </div>
                            @endif

                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300 font-bold text-xs">
                                    <x-heroicon-m-check-circle class="w-4 h-4 shrink-0" />
                                    <span>Salud Financiera & Pasarela</span>
                                </div>
                                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 leading-relaxed">
                                    El <strong>{{ $totalIngresosReservas > 0 ? round(($totalRecaudado / $totalIngresosReservas) * 100, 1) : 100 }}%</strong> de los ingresos facturados han sido cobrados satisfactoriamente.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot:sidebarExtra>
        </x-reportes.layout>
    </div>
</x-filament-panels::page>
