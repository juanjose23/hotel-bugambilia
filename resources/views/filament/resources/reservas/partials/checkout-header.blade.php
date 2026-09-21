<div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs" dusk="checkout-operativo-header">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="p-3.5 bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-2xl">
                <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="w-8 h-8" />
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white">
                        Check-Out: {{ $this->habitacionResumen() }}
                    </h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300">
                        {{ $this->reserva->codigo_reserva }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Titular: <strong class="text-gray-800 dark:text-gray-200">{{ $this->reserva->nombre_cliente ?? 'Huésped' }}</strong>
                    &nbsp;·&nbsp; Estancia: <strong class="text-gray-800 dark:text-gray-200">{{ $this->codigoEstancia() }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold {{ $this->checkoutListo() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' }}"
                dusk="{{ $this->checkoutListo() ? 'checkout-listo' : 'checkout-bloqueado' }}"
            >
                <x-filament::icon :icon="$this->checkoutListo() ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle'" class="w-4 h-4" />
                {{ $this->checkoutListo() ? 'LISTO PARA CHECK-OUT' : 'ACCIONES REQUERIDAS' }}
            </div>

            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-o-arrow-left"
                wire:click="volverALista"
                size="sm"
            >
                Volver a Lista
            </x-filament::button>
        </div>
    </div>
</div>
