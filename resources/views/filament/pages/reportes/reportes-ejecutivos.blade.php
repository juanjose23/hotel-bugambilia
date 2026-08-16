<x-filament-panels::page>
    <div x-data x-on:open-new-tab.window="window.open($event.detail.url || $event.detail[0]?.url, '_blank')" class="w-full space-y-6">

        {{-- Tarjetas KPI Interactivas superior --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- KPI 1: Ingresos Reservados --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Reservado</span>
                    <div class="p-2 bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 rounded-xl">
                        <x-heroicon-o-currency-dollar class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-black text-gray-950 dark:text-white">
                    $ {{ number_format($totalIngresosReservas, 2) }}
                </div>
                <p class="text-[11px] text-gray-500 mt-1">Acumulado en {{ $cantidadReservas }} reservaciones</p>
            </div>

            {{-- KPI 2: Recaudación --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Recaudación Neta</span>
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                    $ {{ number_format($totalRecaudado, 2) }}
                </div>
                <p class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 mt-1">Cobrado vía Stripe y Efectivo</p>
            </div>

            {{-- KPI 3: Cuentas por Cobrar --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cuentas por Cobrar</span>
                    <div class="p-2 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-xl">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400">
                    $ {{ number_format($totalCuentasPorCobrar, 2) }}
                </div>
                <p class="text-[11px] text-rose-600/80 dark:text-rose-400/80 mt-1">Saldos pendientes de cobrar</p>
            </div>

            {{-- KPI 4: Facturación Fiscal --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Facturado Fiscal</span>
                    <div class="p-2 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-xl">
                        <x-heroicon-o-document-check class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-black text-blue-600 dark:text-blue-400">
                    $ {{ number_format($totalFacturadoFiscal, 2) }}
                </div>
                <p class="text-[11px] text-blue-600/80 dark:text-blue-400/80 mt-1">Facturas emitidas oficialmente</p>
            </div>

        </div>

        {{-- Grid Principal de 2 Columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Columna Izquierda: Generador de Reportes Financieros --}}
            <div class="lg:col-span-7 xl:col-span-7 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-4 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="p-3 bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 rounded-2xl shadow-xs ring-1 ring-primary-500/10">
                        <x-heroicon-o-chart-pie class="w-7 h-7" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">Exportación de Reportes Financieros</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Seleccione el informe contable e imprima en PDF para auditoría interna.</p>
                    </div>
                </div>

                <form wire:submit.prevent="descargarReporte" class="space-y-6">
                    <div class="space-y-4">
                        {{ $this->reportForm }}
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <x-filament::button type="submit" color="primary" icon="heroicon-o-document-arrow-down" class="hover:scale-[1.01] transition-transform duration-200 shadow-md">
                            Generar y Descargar PDF
                        </x-filament::button>
                    </div>
                </form>
            </div>

            {{-- Columna Derecha: Recomendaciones Operativas para la Toma de Decisiones --}}
            <div class="lg:col-span-5 xl:col-span-5 space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                        <div class="p-2.5 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-xl">
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
                                    Hay <strong>$ {{ number_format($totalCuentasPorCobrar, 2) }}</strong> pendientes por saldar. Se recomienda enviar recordatorios a huéspedes antes de su fecha de check-out.
                                </p>
                            </div>
                        @endif

                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                            <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300 font-bold text-xs">
                                <x-heroicon-m-check-circle class="w-4 h-4 shrink-0" />
                                <span>Salud Financiera & Stripe</span>
                            </div>
                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 leading-relaxed">
                                El <strong>{{ $totalIngresosReservas > 0 ? round(($totalRecaudado / $totalIngresosReservas) * 100, 1) : 100 }}%</strong> de los ingresos de reservas han sido cobrados satisfactoriamente vía Stripe / pasarela.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
