@use('App\Filament\Pages\Limpieza\AbastecerCarrito', 'AbastecerCarritoPage')

<x-filament-panels::page>
    @if(! $this->carritoId || ! ($carrito = $this->carrito))
        {{-- ===== ESTADO VACÍO ===== --}}
        <x-filament::section>
            <div class="flex flex-col items-center justify-center py-16 text-center gap-5">
                <div class="w-20 h-20 rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <x-heroicon-o-shopping-bag class="w-10 h-10 text-gray-300 dark:text-gray-600" />
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">Carrito no encontrado</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">No se encontró el carrito solicitado o no tienes acceso.</p>
                </div>
                <x-filament::button color="gray" icon="heroicon-o-arrow-left" href="{{ AbastecerCarritoPage::getUrl() }}" tag="a">
                    Volver a la lista de carritos
                </x-filament::button>
            </div>
        </x-filament::section>
    @else
        {{-- ===== HERO — CABECERA ===== --}}
        <x-filament::section class="mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center shrink-0">
                        <x-heroicon-o-shopping-bag class="w-6 h-6 sm:w-8 sm:h-8 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">{{ $carrito->nombre }}</h2>
                            @if($this->bloqueado)
                                <x-filament::badge color="danger" icon="heroicon-o-lock-closed" size="sm">En uso</x-filament::badge>
                            @else
                                <x-filament::badge color="success" icon="heroicon-o-check-circle" size="sm">Disponible</x-filament::badge>
                            @endif
                        </div>
                        @if($carrito->descripcion)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $carrito->descripcion }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 self-start sm:self-auto shrink-0">
                    @if ($this->puedeLiberar && $this->bloqueado)
                        <x-filament::button color="danger" icon="heroicon-o-lock-open" wire:click="prepararLiberacion" size="sm">
                            Liberar Carrito
                        </x-filament::button>
                    @endif

                    @if ($this->isSuperAdmin && ! $this->bloqueado)
                        <x-filament::button color="primary" icon="heroicon-o-user-plus" wire:click="openAssignModal" size="sm">
                            Asignar a Limpieza
                        </x-filament::button>
                    @endif

                    <a href="{{ AbastecerCarritoPage::getUrl() }}"
                       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        Volver a Carritos
                    </a>
                </div>
            </div>

            {{-- Métricas --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
                <x-shared.metric-card
                    :value="$this->totalItems"
                    label="Tipos de Insumo"
                    color="bg-primary-50 dark:bg-primary-900/20"
                    valueColor="text-primary-600 dark:text-primary-400"
                />
                <x-shared.metric-card
                    :value="number_format((float) $this->totalCantidad, 0)"
                    label="Unidades"
                    color="bg-emerald-50 dark:bg-emerald-900/20"
                    valueColor="text-emerald-600 dark:text-emerald-400"
                />
                <x-shared.metric-card
                    :value="$this->totalMovimientos"
                    label="Movimientos"
                    color="bg-gray-100 dark:bg-gray-800/50"
                    valueColor="text-gray-500 dark:text-gray-400"
                />
                @if($this->bloqueado && $this->ejecucionActiva?->fecha)
                    <x-shared.metric-card
                        :value="$this->ejecucionActiva->fecha->format('d/m/Y')"
                        label="Fecha Limpieza"
                        color="bg-orange-50 dark:bg-orange-900/20"
                        valueColor="text-orange-600 dark:text-orange-400"
                    />
                @else
                    <x-shared.metric-card
                        value="Libre"
                        label="Estado"
                        color="bg-green-50 dark:bg-green-900/20"
                        valueColor="text-green-600 dark:text-green-400"
                    />
                @endif
            </div>
        </x-filament::section>

        {{-- ===== INFORMACIÓN DEL CARRITO ===== --}}
        <x-filament::section class="mb-4 sm:mb-6" collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-gray-500" />
                    Información del Carrito
                </div>
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                {{-- Estado --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-700/60">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl shrink-0 flex items-center justify-center
                        @if($this->bloqueado) bg-red-100 dark:bg-red-900/50 @else bg-green-100 dark:bg-green-900/50 @endif">
                        @if($this->bloqueado)
                            <x-heroicon-o-lock-closed class="w-5 h-5 text-red-500 dark:text-red-400" />
                        @else
                            <x-heroicon-o-lock-open class="w-5 h-5 text-green-500 dark:text-green-400" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Estado</p>
                        @if($this->bloqueado)
                            <p class="text-sm font-semibold text-red-600 dark:text-red-400 mt-0.5">Bloqueado — Limpieza activa</p>
                            @if($this->nombreColaborador)
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    <span class="font-medium">Responsable:</span> {{ $this->nombreColaborador }}
                                </p>
                            @endif
                        @else
                            <p class="text-sm font-semibold text-green-600 dark:text-green-400 mt-0.5">Disponible</p>
                        @endif
                    </div>
                </div>

                {{-- Último abastecimiento --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-700/60">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-sky-100 dark:bg-sky-900/50 shrink-0 flex items-center justify-center">
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-sky-500 dark:text-sky-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Último Abastecimiento</p>
                        @if($this->ultimoAbastecimiento)
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-0.5 truncate">{{ $this->ultimoAbastecimientoColaborador }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">
                                {{ $this->ultimoAbastecimiento->created_at?->diffForHumans() ?? 'N/A' }}
                                @if($this->ultimoAbastecimiento->ubicacionOrigen)
                                    · desde {{ $this->ultimoAbastecimiento->ubicacionOrigen->nombre }}
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">Sin abastecimientos registrados</p>
                        @endif
                    </div>
                </div>

                {{-- Actividad reciente --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-700/60">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 shrink-0 flex items-center justify-center">
                        <x-heroicon-o-clock class="w-5 h-5 text-amber-500 dark:text-amber-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Actividad Reciente</p>
                        @if($this->ejecucionActiva)
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-0.5">
                                Limpieza #{{ $this->ejecucionActiva->id }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Inició {{ $this->ejecucionActiva->hora_inicio ? substr($this->ejecucionActiva->hora_inicio, 0, 5) : '—' }}
                                @if($this->ejecucionActiva->turno)
                                    · Turno {{ $this->ejecucionActiva->turno->nombre }}
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">Sin actividad activa</p>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- ===== LAYOUT PRINCIPAL ===== --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            {{-- Stock Actual --}}
            <div class="md:col-span-2 order-last md:order-first space-y-4">
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-cube class="w-5 h-5 text-primary-500" />
                            Stock Actual en Carrito
                            <x-filament::badge size="sm" class="ml-auto">{{ $this->totalItems }} insumos</x-filament::badge>
                        </div>
                    </x-slot>
                    {{ $this->table }}
                </x-filament::section>
            </div>

            {{-- Operaciones --}}
            <div class="md:col-span-1 order-first md:order-last space-y-4">
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-amber-500" />
                            Operaciones
                        </div>
                    </x-slot>

                    @if(! $this->puedeGestionar)
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900">
                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                                <x-heroicon-o-lock-closed class="w-5 h-5 text-red-500" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">Carrito bloqueado</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                                    En uso por <span class="font-medium text-gray-700 dark:text-gray-300">{{ $this->nombreColaborador ?: 'otro colaborador' }}</span>
                                    en una limpieza en progreso.
                                </p>
                            </div>
                        </div>
                    @else
                        <x-shared.tab-switcher
                            activeTab="abastecer"
                            :tabs="[
                                ['id' => 'abastecer', 'label' => 'Abastecer', 'icon' => 'plus-circle', 'activeColor' => 'success'],
                                ['id' => 'devolver', 'label' => 'Devolver', 'icon' => 'arrow-uturn-left', 'activeColor' => 'warning'],
                                ['id' => 'traspasar', 'label' => 'Traspasar', 'icon' => 'arrows-right-left', 'activeColor' => 'info'],
                            ]"
                        />

                        <div class="mt-4">
                            @if($activeTab === 'abastecer')
                                <div class="mb-3 p-3 rounded-lg bg-green-50 dark:bg-green-950/30 border border-green-100 dark:border-green-900">
                                    <p class="text-xs text-green-700 dark:text-green-400">
                                        <x-heroicon-m-information-circle class="w-4 h-4 inline mr-1" />
                                        Seleccione la bodega de origen y los insumos que desea cargar a este carrito.
                                    </p>
                                </div>
                                <form wire:submit.prevent="submitAbastecer" class="space-y-4">
                                    {{ $this->abastecerForm }}
                                    <div class="flex flex-col gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                                        <x-filament::button type="submit" color="success" icon="heroicon-o-plus-circle" class="w-full justify-center">
                                            Cargar Insumos al Carrito
                                        </x-filament::button>
                                    </div>
                                </form>
                            @elseif($activeTab === 'devolver')
                                <div class="mb-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900">
                                    <p class="text-xs text-amber-700 dark:text-amber-400">
                                        <x-heroicon-m-information-circle class="w-4 h-4 inline mr-1" />
                                        Devuelva insumos sobrantes del carrito a una bodega o almacén.
                                    </p>
                                </div>
                                <form wire:submit.prevent="submitDevolver" class="space-y-4">
                                    {{ $this->devolverForm }}
                                    <div class="flex flex-col gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                                        <x-filament::button type="submit" color="warning" icon="heroicon-o-arrow-uturn-left" class="w-full justify-center">
                                            Devolver Insumo a Bodega
                                        </x-filament::button>
                                    </div>
                                </form>
                            @elseif($activeTab === 'traspasar')
                                <div class="mb-3 p-3 rounded-lg bg-sky-50 dark:bg-sky-950/30 border border-sky-100 dark:border-sky-900">
                                    <p class="text-xs text-sky-700 dark:text-sky-400">
                                        <x-heroicon-m-information-circle class="w-4 h-4 inline mr-1" />
                                        Transfiera insumos entre carritos de limpieza directamente.
                                    </p>
                                </div>
                                <form wire:submit.prevent="submitTraspasar" class="space-y-4">
                                    {{ $this->traspasarForm }}
                                    <div class="flex flex-col gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                                        <x-filament::button type="submit" color="info" icon="heroicon-o-arrows-right-left" class="w-full justify-center">
                                            Realizar Traspaso
                                        </x-filament::button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endif
                </x-filament::section>
            </div>
        </div>

        {{-- ===== HISTORIAL DE MOVIMIENTOS ===== --}}
        <div class="mt-4 sm:mt-6">
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clock class="w-5 h-5 text-gray-500" />
                        Historial de Movimientos
                        <x-filament::badge size="sm" class="ml-auto">{{ $this->totalMovimientos }} total</x-filament::badge>
                    </div>
                </x-slot>

                <x-shared.movements-table :movimientos="$this->movimientos" />
            </x-filament::section>
        </div>
    @endif

    {{-- Modal para Asignar Carrito a Limpieza --}}
    <x-filament::modal id="assign-carrito-modal" width="md">
        <x-slot name="heading">Asignar Carrito a Limpieza</x-slot>
        <x-slot name="description">Asigne este carrito a una tarea de limpieza pendiente.</x-slot>

        <form wire:submit.prevent="confirmAssign" class="space-y-5">
            {{ $this->assignForm }}

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button color="gray" wire:click="closeAssignModal" type="button">Cancelar</x-filament::button>
                <x-filament::button type="submit" color="primary">Confirmar Asignación</x-filament::button>
            </div>
        </form>
    </x-filament::modal>

    {{-- Modal de Confirmación para Liberar Carrito --}}
    <x-filament::modal id="confirm-liberar-modal" width="md">
        <x-slot name="heading">Confirmar Liberación de Carrito</x-slot>
        <x-slot name="description">Revise los detalles de la asignación actual del carrito antes de liberarlo.</x-slot>

        <div class="space-y-4">
            @if ($liberarWarningData)
                @if ($liberarWarningData['is_active'])
                    <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 rounded-xl space-y-2">
                        <div class="flex items-center gap-2 text-red-600 dark:text-red-400 font-bold text-sm">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span>¡Cuidado! Limpieza Activa</span>
                        </div>
                        <p class="text-xs text-red-700 dark:text-red-300 leading-relaxed font-semibold">
                            Este carrito está siendo utilizado actualmente en una limpieza en progreso.
                        </p>
                        <div class="text-xs text-red-700 dark:text-red-300 space-y-1 pt-1 border-t border-red-200/30 dark:border-red-900/30">
                            <span class="font-bold block mb-1">Consecuencias de esta acción:</span>
                            <ul class="list-disc list-inside space-y-0.5 ml-1">
                                <li>La limpieza activa se **cancelará** y volverá a <strong>Pendiente</strong>.</li>
                                <li>El colaborador asignado será removido de esta tarea de limpieza.</li>
                                <li>La habitación/área regresará a su estado original de suciedad.</li>
                                <li>Se perderá la fecha de inicio registrada para esta tarea.</li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 rounded-xl space-y-2">
                        <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold text-sm">
                            <x-heroicon-o-information-circle class="w-5 h-5" />
                            <span>Carrito Asignado</span>
                        </div>
                        <p class="text-xs text-amber-700 dark:text-amber-300 leading-relaxed font-semibold">
                            Este carrito está asignado a una tarea de limpieza pendiente.
                        </p>
                        <div class="text-xs text-amber-700 dark:text-amber-300 space-y-1 pt-1 border-t border-amber-200/30 dark:border-amber-900/30">
                            <span class="font-bold block mb-1">Consecuencias de esta acción:</span>
                            <ul class="list-disc list-inside space-y-0.5 ml-1">
                                <li>Se desvinculará el carrito de la tarea de limpieza pendiente.</li>
                                <li>La tarea continuará como **Pendiente** lista para recibir otro carrito.</li>
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl space-y-2 text-xs">
                    <div>
                        <span class="font-semibold text-gray-500 dark:text-gray-400">Tarea de Limpieza:</span>
                        <span class="font-medium text-gray-900 dark:text-white block mt-0.5">Ejecución #{{ $liberarWarningData['ejecucion_id'] }} - {{ $liberarWarningData['area'] }}</span>
                    </div>
                    <div class="pt-1">
                        <span class="font-semibold text-gray-500 dark:text-gray-400">Colaborador Asignado:</span>
                        <span class="font-medium text-gray-900 dark:text-white block mt-0.5">{{ $liberarWarningData['colaborador'] }}</span>
                    </div>
                    <div class="pt-1">
                        <span class="font-semibold text-gray-500 dark:text-gray-400">Estado de Limpieza:</span>
                        <span class="font-medium text-gray-900 dark:text-white block mt-0.5">{{ $liberarWarningData['estado'] }}</span>
                    </div>
                </div>

                @if ($liberarWarningData['is_active'])
                    <p class="text-xs text-gray-600 dark:text-gray-400 italic leading-relaxed">
                        Si libera el carrito, la tarea de limpieza activa se **cancelará y regresará al estado de Pendiente** (liberando también al colaborador asignado y restaurando el estado original de la habitación/área). ¿Desea continuar?
                    </p>
                @else
                    <p class="text-xs text-gray-600 dark:text-gray-400 italic leading-relaxed">
                        ¿Desea desvincular este carrito de la tarea de limpieza asignada?
                    </p>
                @endif
            @else
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    ¿Está seguro de que desea liberar este carrito de limpieza?
                </p>
            @endif

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'confirm-liberar-modal' })" type="button">Cancelar</x-filament::button>
                <x-filament::button color="danger" wire:click="liberarCarrito" type="button">Confirmar Liberación</x-filament::button>
            </div>
        </div>
    </x-filament::modal>
</x-filament-panels::page>
