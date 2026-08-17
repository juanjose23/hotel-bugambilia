<x-filament-panels::page>
    <div x-data x-on:open-new-tab.window="window.open($event.detail.url || $event.detail[0]?.url, '_blank')" class="w-full space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Generador de Reportes --}}
            <div class="lg:col-span-7 xl:col-span-7 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-4 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="p-3 bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 rounded-2xl shadow-xs ring-1 ring-primary-500/10">
                        <x-heroicon-o-document-chart-bar class="w-7 h-7" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Reportes de Inventario & Almacenes</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Descargue reportes de stock, productos vencidos, valorización y mermas en PDF o Excel.</p>
                    </div>
                </div>

                <form wire:submit.prevent="descargarReporte" class="space-y-6">
                    <div class="space-y-4">
                        {{ $this->reportForm }}
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <x-filament::button type="submit" color="primary" icon="heroicon-o-document-arrow-down" class="hover:scale-[1.01] transition-transform duration-200 shadow-md">
                            Generar y Descargar Reporte
                        </x-filament::button>
                    </div>
                </form>
            </div>

            {{-- Resumen Informativo --}}
            <div class="lg:col-span-5 xl:col-span-5 space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                        <div class="p-2.5 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-xl">
                            <x-heroicon-o-archive-box class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">Control de Stock y Bodegas</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Formatos disponibles PDF y Excel</p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        Exporte informes de stock mínimo, trazabilidad de lotes, cuarentena y valorización en tiempo real para auditorías de inventario y consumos operativos.
                    </p>
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
