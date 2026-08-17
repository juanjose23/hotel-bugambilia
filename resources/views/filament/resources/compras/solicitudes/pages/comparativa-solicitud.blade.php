<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Encabezado de la Solicitud --}}
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitud: {{ $record->codigo }}</h2>
                    <p class="text-sm text-gray-500">{{ $record->motivo }}</p>
                </div>
                <div class="flex gap-2">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                        Total Ítems: {{ $record->items->count() }}
                    </span>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                        Cotizaciones: {{ $record->cotizaciones->count() }}
                    </span>
                </div>
            </div>
        </div>

        @php $cotizaciones = $this->getCotizaciones(); @endphp

        @if(count($cotizaciones) > 0)
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($cotizaciones as $cot)
                    <div @class([
                        'relative p-6 transition-all border-2 rounded-2xl bg-white dark:bg-gray-900',
                        'border-primary-500 shadow-xl ring-4 ring-primary-500/10' => $cot['es_ganadora'],
                        'border-amber-400 shadow-lg ring-4 ring-amber-400/20' => $cot['es_recomendada'] && !$cot['es_ganadora'],
                        'border-gray-200 dark:border-gray-800 hover:border-primary-300 shadow-sm' => !$cot['es_ganadora'] && !$cot['es_recomendada'],
                    ])>
                        {{-- Badge de Ganadora --}}
                        @if($cot['es_ganadora'])
                            <div class="absolute top-0 right-0 p-2 transform translate-x-2 -translate-y-2">
                                <span class="flex items-center gap-1 px-3 py-1 text-xs font-bold text-white uppercase bg-primary-500 rounded-full shadow-md">
                                    <x-heroicon-s-check-badge class="w-4 h-4" /> GANADORA
                                </span>
                            </div>
                        @endif

                        {{-- Badge de Recomendada --}}
                        @if($cot['es_recomendada'] && !$cot['es_ganadora'])
                            <div class="absolute top-0 left-0 p-2 transform -translate-x-2 -translate-y-2">
                                <span class="flex items-center gap-1 px-3 py-1 text-xs font-bold text-white uppercase bg-amber-500 rounded-full shadow-md animate-bounce">
                                    <x-heroicon-s-check-badge class="w-4 h-4" /> RECOMENDADA
                                </span>
                            </div>
                        @endif

                        <div class="space-y-4">
                            {{-- Proveedor --}}
                            <div class="flex items-start gap-3">
                                <div @class([
                                    'p-2 rounded-lg',
                                    'bg-primary-50 dark:bg-primary-900/30' => $cot['es_ganadora'],
                                    'bg-amber-50 dark:bg-amber-900/30' => $cot['es_recomendada'] && !$cot['es_ganadora'],
                                    'bg-gray-100 dark:bg-gray-800' => !$cot['es_ganadora'] && !$cot['es_recomendada'],
                                ])>
                                    <x-heroicon-o-user-group @class([
                                        'w-6 h-6',
                                        'text-primary-600' => $cot['es_ganadora'],
                                        'text-amber-600' => $cot['es_recomendada'] && !$cot['es_ganadora'],
                                        'text-gray-500' => !$cot['es_ganadora'] && !$cot['es_recomendada'],
                                    ]) />
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white line-clamp-1">{{ $cot['proveedor'] }}</h3>
                                    <p class="text-xs text-gray-500 uppercase">{{ $cot['empresa'] }}</p>
                                </div>
                            </div>

                            <hr class="border-gray-100 dark:border-gray-800">

                            {{-- Métricas Clave --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Costo Total</p>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-xl font-bold @if($cot['es_mas_barato']) text-success-600 @else text-gray-900 dark:text-white @endif">
                                            ${{ number_format($cot['total'], 2) }}
                                        </span>
                                        @if($cot['es_mas_barato'])
                                            <x-heroicon-s-banknotes class="w-4 h-4 text-success-500" title="Mejor Precio" />
                                        @endif
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Tiempo Entrega</p>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-xl font-bold @if($cot['es_mas_rapido']) text-info-600 @else text-gray-900 dark:text-white @endif">
                                            {{ $cot['dias_entrega'] }}
                                        </span>
                                        <span class="text-xs text-gray-400">días</span>
                                        @if($cot['es_mas_rapido'])
                                            <x-heroicon-s-bolt class="w-4 h-4 text-info-500" title="Más Rápido" />
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Condiciones --}}
                            <div class="flex items-center gap-2 p-3 border border-gray-100 rounded-xl dark:border-gray-800">
                                <x-heroicon-o-credit-card class="w-5 h-5 text-gray-400" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Pago: <span class="font-bold text-gray-900 dark:text-white">{{ $cot['condicion_pago'] }}</span>
                                </span>
                            </div>

                            {{-- Acción --}}
                            <div @class(['pt-4', 'pb-2' => $cot['es_ganadora']])>
                                @if(!$cot['es_ganadora'])
                                    <a 
                                        href="{{ \App\Filament\Resources\Compras\Cotizaciones\CotizacionResource::getUrl('index', ['tableFilters' => ['solicitud_id' => ['value' => $record->id]]]) }}"
                                        @class([
                                            'block w-full px-4 py-2 text-sm font-bold text-center text-white transition-all rounded-xl focus:ring-4',
                                            'bg-amber-500 hover:bg-amber-600 focus:ring-amber-500/20 shadow-amber-200' => $cot['es_recomendada'],
                                            'bg-gray-900 hover:bg-primary-600 focus:ring-primary-500/20 dark:bg-gray-700 dark:hover:bg-primary-600' => !$cot['es_recomendada'],
                                        ])
                                    >
                                        {{ $cot['es_recomendada'] ? 'Gestionar Recomendación' : 'Ver en Cotizaciones' }}
                                    </a>
                                @else
                                    <div class="flex flex-col items-center justify-center p-2 text-sm font-bold text-primary-600 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                                        <x-heroicon-m-check-badge class="w-5 h-5 mb-1" /> Oferta Seleccionada
                                        <p class="text-[10px] text-gray-400 uppercase font-normal">Listo para generar Orden</p>
                                        <a href="{{ \App\Filament\Resources\Compras\Cotizaciones\CotizacionResource::getUrl('index', ['tableFilters' => ['solicitud_id' => ['value' => $record->id]]]) }}" class="mt-2 text-[11px] underline hover:text-primary-700">Ver Detalles</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 dark:bg-gray-900/50 dark:border-gray-800">
                <x-heroicon-o-document-magnifying-glass class="w-16 h-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Sin Cotizaciones Disponibles</h3>
                <p class="text-gray-500">Debe registrar al menos una cotización para realizar la comparativa.</p>
                <div class="mt-6">
                    <x-filament::button 
                        color="gray" 
                        icon="heroicon-o-plus"
                        tag="a"
                        href="{{ \App\Filament\Resources\Compras\Cotizaciones\CotizacionResource::getUrl('create', ['solicitud_id' => $record->id]) }}"
                    >
                        Registrar Primera Cotización
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
