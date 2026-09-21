@use(App\Support\MonedaHelper)
<div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Resumen del Folio</span>

    <div class="p-4 rounded-xl {{ $this->saldoCuenta() > 0 ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' }}">
        <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Saldo a Cobrar</span>
        <div class="text-3xl font-black mt-1 {{ $this->saldoCuenta() > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ MonedaHelper::formatear($this->saldoCuenta(), $this->cuentaActiva()?->moneda) }}
        </div>
        <p class="text-xs mt-1 {{ $this->saldoCuenta() > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ $this->saldoCuenta() > 0 ? 'Cobro pendiente antes de entregar comprobante' : 'Cuenta totalmente liquidada' }}
        </p>
    </div>

    <div class="grid grid-cols-2 gap-3 text-xs">
        <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
            <span class="text-gray-400 font-medium">Total Cargos</span>
            <p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ MonedaHelper::formatear($this->montoCuenta('total'), $this->cuentaActiva()?->moneda) }}</p>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
            <span class="text-gray-400 font-medium">Total Pagado</span>
            <p class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ MonedaHelper::formatear($this->montoCuenta('total_pagado'), $this->cuentaActiva()?->moneda) }}</p>
        </div>
    </div>

    @if ($this->saldoCuenta() > 0)
        <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Opciones de Pago & Liquidación</span>
            <div class="grid grid-cols-1 gap-2">
                <x-filament::button
                    type="button"
                    color="success"
                    icon="heroicon-o-banknotes"
                    wire:click="mountAction('registrar_pago')"
                    size="sm"
                    class="w-full justify-start text-xs font-semibold"
                >
                    Pago Simple (Efectivo / POS)
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="warning"
                    icon="heroicon-o-arrows-pointing-out"
                    wire:click="mountAction('registrar_pagos_multiples')"
                    size="sm"
                    class="w-full justify-start text-xs font-semibold"
                >
                    Pago Dividido / Múltiples Métodos
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="primary"
                    icon="heroicon-o-credit-card"
                    wire:click="mountAction('pagar_stripe')"
                    size="sm"
                    class="w-full justify-start text-xs font-semibold"
                >
                    Cobro con Pasarela Stripe 💳
                </x-filament::button>
            </div>
        </div>
    @endif
</div>
