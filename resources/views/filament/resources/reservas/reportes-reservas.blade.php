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
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Inteligencia de Reservas & Ventas</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Configure los criterios de análisis y descargue reportes detallados en formato PDF.</p>
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

            {{-- Guía Rápida & Métricas --}}
            <div class="lg:col-span-5 xl:col-span-5 space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                        <div class="p-2.5 bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 rounded-xl">
                            <x-heroicon-o-information-circle class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">Información y Auditoría</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Parámetros de exportación oficial</p>
                        </div>
                    </div>

                    <ul class="space-y-3 text-xs text-gray-600 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-primary-500 mt-1.5"></span>
                            <span><strong>Ocupación:</strong> Muestra el volumen de noches y estadías de huéspedes en el período.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5"></span>
                            <span><strong>Ventas:</strong> Filtra cobros realizados vía pasarela Stripe, transferencias bancarias y efectivo.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5"></span>
                            <span><strong>Huéspedes:</strong> Genera la lista de titulares con récord de estadías en Hotel Bugambilias.</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
