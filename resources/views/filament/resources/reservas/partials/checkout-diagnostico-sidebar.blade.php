<div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Diagnóstico de Salida</span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-md {{ $this->checkoutListo() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
            {{ $this->checkoutListo() ? 'Aprobado' : 'Pendiente' }}
        </span>
    </div>

    <div class="space-y-2.5 text-xs font-medium">
        <div class="flex items-center justify-between p-2.5 rounded-xl {{ (bool) ($this->data['consumos_revisados'] ?? false) ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300' }}">
            <span class="flex items-center gap-2">
                <x-filament::icon :icon="(bool) ($this->data['consumos_revisados'] ?? false) ? 'heroicon-o-check-circle' : 'heroicon-o-clock'" class="w-4 h-4" />
                Consumos y Minibar
            </span>
            <span class="font-bold">{{ (bool) ($this->data['consumos_revisados'] ?? false) ? 'Verificado' : 'Falta validar' }}</span>
        </div>

        <div class="flex items-center justify-between p-2.5 rounded-xl {{ $this->saldoPermitido() ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300' : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300' }}">
            <span class="flex items-center gap-2">
                <x-filament::icon :icon="$this->saldoPermitido() ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" class="w-4 h-4" />
                Balance Financiero
            </span>
            <span class="font-bold">{{ $this->saldoPermitido() ? 'Liquidado' : 'Requiere Cobro' }}</span>
        </div>

        <div class="flex items-center justify-between p-2.5 rounded-xl {{ $this->llavesPermitidas() ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300' }}">
            <span class="flex items-center gap-2">
                <x-filament::icon :icon="$this->llavesPermitidas() ? 'heroicon-o-check-circle' : 'heroicon-o-key'" class="w-4 h-4" />
                Llaves Devueltas
            </span>
            <span class="font-bold">{{ $this->llavesPermitidas() ? 'Completas' : 'Faltan tarjetas' }}</span>
        </div>
    </div>

    @unless ($this->checkoutListo())
        <div class="p-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-xl text-xs">
            <strong>Pendiente:</strong> {{ $this->motivoBloqueo() }}
        </div>
    @endunless
</div>
