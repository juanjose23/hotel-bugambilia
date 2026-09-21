<x-filament-panels::page>
    <div class="space-y-6">

        @if ($this->reserva === null)
            {{-- ══ Dashboard & Lista de Recepción (Salidas) ══ --}}
            @include('filament.resources.reservas.partials.checkout-metricas')

            {{-- Tabla Principal de Estancias Activas --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                            <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Estancias Activas — Control de Salidas</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Seleccione una habitación para abrir el Centro de Comando de Check-Out</p>
                        </div>
                    </div>
                </div>
                {{ $this->table }}
            </div>

        @else
            {{-- ══ COMMAND CENTER SPLIT-PANE DE CHECK-OUT ══ --}}
            <form wire:submit.prevent="submit" class="space-y-6">

                {{-- Banner Superior Ejecutivo --}}
                @include('filament.resources.reservas.partials.checkout-header')

                {{-- Split-Pane: 2 Columnas (8 cols Principal / 4 cols Terminal Sticky) --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    {{-- Columna Principal: Formularios y Desgloses (8 cols) --}}
                    <div class="lg:col-span-8 space-y-6">
                        {{ $this->form }}
                    </div>

                    {{-- Columna Lateral: Terminal Sticky de Cobro y Cierre (4 cols) --}}
                    <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-6">

                        {{-- Card de Diagnóstico / Semáforo --}}
                        @include('filament.resources.reservas.partials.checkout-diagnostico-sidebar')

                        {{-- Card de Balance Financiero & Botones de Cobro --}}
                        @include('filament.resources.reservas.partials.checkout-folio-sidebar')

                        {{-- Botón Principal y Acciones de Cierre --}}
                        <div class="space-y-3">
                            <x-filament::button
                                type="submit"
                                color="{{ $this->checkoutListo() ? 'success' : 'warning' }}"
                                icon="heroicon-o-check-circle"
                                size="lg"
                                class="w-full py-4 text-base font-black shadow-lg hover:shadow-xl transition-all"
                                dusk="btn-confirmar-checkout"
                            >
                                {{ $this->checkoutListo() ? 'CONFIRMAR CHECK-OUT' : 'FORZAR SALIDA / VALIDAR' }}
                            </x-filament::button>

                            <p class="text-[11px] text-center text-gray-500 dark:text-gray-400">
                                Al confirmar, la habitación cambiará automáticamente a estado <span class="font-semibold text-amber-600 dark:text-amber-400">Sucia</span> y la cuenta quedará cerrada.
                            </p>
                        </div>

                    </div>

                </div>

            </form>
        @endif

    </div>
</x-filament-panels::page>
