<x-filament-panels::page>
    @if(! $this->carritoId || ! ($carrito = $this->carrito))
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">
                No se encontró el carrito solicitado.
                <a href="{{ \App\Filament\Pages\Limpieza\AbastecerCarrito::getUrl() }}"
                   class="text-primary-600 hover:underline ml-1">Volver a la lista de carritos</a>
            </p>
        </x-filament::section>
    @else
        {{-- ===== CABECERA ===== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ \App\Filament\Pages\Limpieza\AbastecerCarrito::getUrl() }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Volver a Carritos
                </a>
            </div>

            @php
                $bloqueado = \App\Models\Limpieza\LimpiezaEjecucion::where('estado', \App\Enums\HabitacionesEspacios\EstadoLimpieza::EnProgreso)
                    ->where('carrito_id', $carrito->id)->exists();
                $totalItems = $this->getStocks()->count();
            @endphp

            <div class="flex items-center gap-3">
                @if($bloqueado)
                    <x-filament::badge color="danger" icon="heroicon-o-lock-closed">
                        En uso — Limpieza en progreso
                    </x-filament::badge>
                @else
                    <x-filament::badge color="success" icon="heroicon-o-lock-open">
                        Disponible
                    </x-filament::badge>
                @endif

                <x-filament::badge color="info" icon="heroicon-o-cube">
                    {{ $totalItems }} {{ $totalItems === 1 ? 'insumo' : 'insumos' }} en carrito
                </x-filament::badge>
            </div>
        </div>

        {{-- ===== LAYOUT PRINCIPAL ===== --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ===== COLUMNA IZQ: STOCK ACTUAL ===== --}}
            <div class="xl:col-span-1 space-y-4">
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-archive-box class="w-5 h-5 text-primary-500" />
                            Stock Actual a Bordo
                        </div>
                    </x-slot>

                    @php $stocks = $this->getStocks(); @endphp

                    @if($stocks->isEmpty())
                        <div class="text-center py-8">
                            <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                El carrito no tiene insumos actualmente.
                            </p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($stocks as $st)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $st->variante?->producto?->nombre ?? 'Insumo sin nombre' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            @if($st->variante?->nombre_variante)
                                                {{ $st->variante->nombre_variante }} &bull;
                                            @endif
                                            Lote: {{ $st->lote?->codigo_lote ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="ml-3 flex-shrink-0">
                                        <x-filament::badge
                                            color="{{ $st->cantidad > 5 ? 'success' : ($st->cantidad > 0 ? 'warning' : 'danger') }}"
                                        >
                                            {{ $st->cantidad }}
                                        </x-filament::badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            </div>

            {{-- ===== COLUMNA DER: OPERACIONES ===== --}}
            <div class="xl:col-span-2 space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-primary-500" />
                            Operaciones de Administración
                        </div>
                    </x-slot>

                    {{-- Tabs de Filament nativas --}}
                    <x-filament::tabs label="Operaciones" wire:model.live="activeTab">
                        <x-filament::tabs.item value="abastecer" icon="heroicon-o-plus-circle">
                            Abastecer
                        </x-filament::tabs.item>
                        <x-filament::tabs.item value="devolver" icon="heroicon-o-arrow-uturn-left">
                            Devolver a Bodega
                        </x-filament::tabs.item>
                        <x-filament::tabs.item value="traspasar" icon="heroicon-o-arrows-right-left">
                            Traspasar a Carrito
                        </x-filament::tabs.item>
                    </x-filament::tabs>

                    <div class="mt-6">
                        @if($activeTab === 'abastecer')
                            <form wire:submit.prevent="submitAbastecer" class="space-y-5">
                                {{ $this->abastecerForm }}
                                <div class="flex justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ \App\Filament\Pages\Limpieza\AbastecerCarrito::getUrl() }}">
                                        <x-filament::button color="gray" outlined>
                                            Cancelar
                                        </x-filament::button>
                                    </a>
                                    <x-filament::button type="submit" color="success" icon="heroicon-o-plus-circle">
                                        Cargar Insumos al Carrito
                                    </x-filament::button>
                                </div>
                            </form>

                        @elseif($activeTab === 'devolver')
                            <form wire:submit.prevent="submitDevolver" class="space-y-5">
                                {{ $this->devolverForm }}
                                <div class="flex justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ \App\Filament\Pages\Limpieza\AbastecerCarrito::getUrl() }}">
                                        <x-filament::button color="gray" outlined>
                                            Cancelar
                                        </x-filament::button>
                                    </a>
                                    <x-filament::button type="submit" color="warning" icon="heroicon-o-arrow-uturn-left">
                                        Devolver Insumo
                                    </x-filament::button>
                                </div>
                            </form>

                        @elseif($activeTab === 'traspasar')
                            <form wire:submit.prevent="submitTraspasar" class="space-y-5">
                                {{ $this->traspasarForm }}
                                <div class="flex justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ \App\Filament\Pages\Limpieza\AbastecerCarrito::getUrl() }}">
                                        <x-filament::button color="gray" outlined>
                                            Cancelar
                                        </x-filament::button>
                                    </a>
                                    <x-filament::button type="submit" color="info" icon="heroicon-o-arrows-right-left">
                                        Realizar Traspaso
                                    </x-filament::button>
                                </div>
                            </form>
                        @endif
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- ===== HISTORIAL DE MOVIMIENTOS ===== --}}
        <div class="mt-6">
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clock class="w-5 h-5 text-gray-500" />
                        Historial Reciente de Movimientos
                    </div>
                </x-slot>

                @php $movs = $this->getMovimientos(); @endphp

                @if($movs->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">
                        No se han registrado movimientos para este carrito.
                    </p>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Insumo</th>
                                    <th class="px-4 py-3 text-right">Cantidad</th>
                                    <th class="px-4 py-3">Origen</th>
                                    <th class="px-4 py-3">Destino</th>
                                    <th class="px-4 py-3">Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($movs as $mov)
                                    @php
                                        $user = 'N/A';
                                        if ($mov->creadoPor) {
                                            $p = $mov->creadoPor->persona;
                                            $user = $p
                                                ? trim($p->primer_nombre . ' ' . ($p->personaNatural->primer_apellido ?? ''))
                                                : $mov->creadoPor->name;
                                        }
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                            {{ $mov->created_at?->format('d/m/Y H:i') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-filament::badge
                                                color="{{ str_contains($mov->tipo, 'TRASLADO') ? 'info' : (str_contains($mov->tipo, 'AJUSTE') ? 'warning' : 'gray') }}"
                                                size="sm"
                                            >
                                                {{ $mov->tipo }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-white">
                                            {{ $mov->producto?->nombre ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-xs">
                                            {{ $mov->cantidad }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            {{ $mov->ubicacionOrigen?->nombre ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            {{ $mov->ubicacionDestino?->nombre ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">{{ $user }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
