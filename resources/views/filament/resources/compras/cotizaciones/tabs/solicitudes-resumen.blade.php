@php
    $solicitudes = $solicitudes ?? collect();
@endphp

<div class="p-6 space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($solicitudes as $solicitud)
            <div class="flex flex-col p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-2 py-1 text-[10px] font-bold text-primary-600 bg-primary-50 rounded-full dark:bg-primary-900/30">
                        {{ $solicitud->codigo }}
                    </span>
                    <span @class([
                        'px-2 py-1 text-[10px] font-bold rounded-full',
                        'bg-success-50 text-success-600 dark:bg-success-900/30' => $solicitud->cotizaciones_count >= 2,
                        'bg-warning-50 text-warning-600 dark:bg-warning-900/30' => $solicitud->cotizaciones_count < 2 && $solicitud->cotizaciones_count > 0,
                        'bg-gray-50 text-gray-600 dark:bg-gray-900/30' => $solicitud->cotizaciones_count == 0,
                    ])>
                        {{ $solicitud->cotizaciones_count }} Cotizaciones
                    </span>
                </div>

                <h3 class="mb-2 text-sm font-bold text-gray-900 dark:text-white line-clamp-1">
                    {{ $solicitud->motivo }}
                </h3>

                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400 italic">
                    {{ $solicitud->colaborador->persona->nombre_completo }}
                </p>

                <div class="mt-auto">
                    @if($solicitud->cotizaciones_count >= 1)
                        <a 
                            href="{{ \App\Filament\Resources\Compras\Cotizaciones\CotizacionResource::getUrl('comparativa', ['solicitud_id' => $solicitud->id]) }}"
                            class="flex items-center justify-center w-full px-4 py-2 text-xs font-bold text-white transition-all bg-primary-600 rounded-lg hover:bg-primary-700"
                        >
                            <x-heroicon-o-arrows-right-left class="w-4 h-4 mr-2" />
                            Comparar Precios
                        </a>
                    @else
                        <a 
                            href="{{ \App\Filament\Resources\Compras\Cotizaciones\CotizacionResource::getUrl('create', ['solicitud_id' => $solicitud->id]) }}"
                            class="flex items-center justify-center w-full px-4 py-2 text-xs font-bold text-gray-700 transition-all bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                            Agregar Cotización
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($solicitudes->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 dark:bg-gray-900/50 dark:border-gray-800">
            <x-heroicon-o-document-text class="w-16 h-16 text-gray-300 mb-4" />
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">No hay solicitudes aprobadas</h3>
            <p class="text-gray-500 text-sm">Las solicitudes aprobadas aparecerán aquí para ser cotizadas o comparadas.</p>
        </div>
    @endif
</div>
