<x-filament-panels::page>
    <div class="space-y-6">
        @if($recomendacion)
            <div @class([
                'p-4 rounded-xl border-2 flex items-start gap-4 shadow-sm',
                'bg-success-50 border-success-200 dark:bg-success-900/20 dark:border-success-800' => $recomendacion['color'] === 'success',
                'bg-warning-50 border-warning-200 dark:bg-warning-900/20 dark:border-warning-800' => $recomendacion['color'] === 'warning',
            ])>
                <div @class([
                    'p-2 rounded-lg',
                    'bg-success-500 text-white' => $recomendacion['color'] === 'success',
                    'bg-warning-500 text-white' => $recomendacion['color'] === 'warning',
                ])>
                    <x-heroicon-o-light-bulb class="w-6 h-6" />
                </div>
                <div>
                    <h4 @class([
                        'text-sm font-bold uppercase tracking-wider mb-1',
                        'text-success-700 dark:text-success-400' => $recomendacion['color'] === 'success',
                        'text-warning-700 dark:text-warning-400' => $recomendacion['color'] === 'warning',
                    ])>
                        RECOMENDACIÓN DEL SISTEMA: {{ $recomendacion['tipo'] }}
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 italic">
                        {!! $recomendacion['mensaje'] !!}
                    </p>
                    @if($recomendacion['ahorro'] > 0)
                        <p class="mt-2 text-xs font-bold text-success-600 dark:text-success-400">
                            Ahorro potencial estimado: ${{ number_format($recomendacion['ahorro'], 2) }}
                        </p>
                    @endif
                </div>
            </div>
        @endif

        @php $data = $this->getComparisonData(); @endphp

        @if(count($data['rows']) > 0)
            <div class="overflow-hidden border border-gray-200 rounded-xl shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="p-4 text-sm font-bold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">Producto / Ítem</th>
                            @foreach($data['cotizaciones'] as $cot)
                                <th class="p-4 text-sm font-bold text-center border-b border-gray-200 dark:border-gray-700">
                                     <div class="flex flex-col items-center gap-2">
                                         <span class="text-gray-900 dark:text-white leading-tight">
                                             {{ $cot->proveedor->persona->personaJuridica->razon_social ?? $cot->proveedor->contacto_nombre }}
                                         </span>
                                         <span class="px-2 py-0.5 text-[9px] font-bold text-gray-500 bg-gray-100 rounded dark:bg-gray-800 uppercase tracking-tighter">
                                             {{ $cot->proveedor->tipoProveedor->nombre ?? 'Proveedor' }}
                                         </span>
                                         @if($cot->proveedor->persona->personaJuridica?->numero_identificacion)
                                            <span class="text-[10px] text-gray-400 font-mono">
                                                {{ strtoupper($cot->proveedor->persona->personaJuridica->tipo_identificacion ?? 'ID') }}: {{ $cot->proveedor->persona->personaJuridica->numero_identificacion }}
                                            </span>
                                         @endif
                                        <button 
                                            wire:click="seleccionarTodoProveedor({{ $cot->id }})"
                                            class="px-2 py-1 text-[10px] text-white bg-primary-600 rounded hover:bg-primary-700 transition-colors"
                                        >
                                            Elegir Todo
                                        </button>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($data['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="p-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $row['producto'] }}</div>
                                    <div class="text-[10px] text-primary-600 font-bold uppercase">{{ $row['variante_solicitada'] }}</div>
                                    <div class="text-xs text-gray-500">Cantidad: {{ number_format($row['cantidad'], 0) }}</div>
                                </td>
                                @foreach($data['cotizaciones'] as $cot)
                                    <td @class([
                                        'p-4 text-center text-sm',
                                        'bg-success-50/30 dark:bg-success-900/10' => $row['mejor_cotizacion_id'] == $cot->id
                                    ])>
                                        @if($row['precios'][$cot->id] !== null)
                                            <div class="flex flex-col items-center gap-1">
                                                <span @class([
                                                    'font-bold text-base',
                                                    'text-success-600 dark:text-success-400' => $row['mejor_cotizacion_id'] == $cot->id,
                                                    'text-gray-900 dark:text-white' => $row['mejor_cotizacion_id'] != $cot->id
                                                ])>
                                                    ${{ number_format($row['precios'][$cot->id], 2) }}
                                                </span>
                                                
                                                <span class="text-[9px] text-gray-400 font-medium uppercase tracking-tighter line-clamp-1" title="Variante ofrecida">
                                                    {{ $row['variantes_ofrecidas'][$cot->id] }}
                                                </span>
                                                
                                                @php 
                                                    $thisItem = $cot->items->where('producto_id', $row['producto_id'])->first();
                                                    $isElected = $thisItem?->es_elegido ?? false;
                                                @endphp

                                                <button 
                                                    wire:click="seleccionarGanadorPorItem({{ $row['producto_id'] }}, {{ $cot->id }})"
                                                    @class([
                                                        'p-1 rounded-full transition-all',
                                                        'bg-success-500 text-white' => $isElected,
                                                        'bg-gray-100 text-gray-400 hover:bg-primary-100 hover:text-primary-600' => !$isElected
                                                    ])
                                                >
                                                    <x-heroicon-s-check-circle class="w-5 h-5" />
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs italic">No cotizado</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td class="p-4 font-bold text-gray-900 dark:text-white text-right">Totales Brutos</td>
                            @foreach($data['cotizaciones'] as $cot)
                                <td class="p-4 text-center font-bold text-primary-600">
                                    ${{ number_format($cot->total, 2) }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl dark:bg-amber-900/20 dark:border-amber-800">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-amber-100 rounded-lg dark:bg-amber-900/40">
                        <x-heroicon-o-light-bulb class="w-6 h-6 text-amber-600" />
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-900 dark:text-amber-400">Estrategia de Compra Dividida</h4>
                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-500">
                            Si seleccionas los mejores precios de cada proveedor, el sistema generará órdenes de compra independientes para cada uno, optimizando el presupuesto total.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 dark:bg-gray-900/50 dark:border-gray-800">
                <x-heroicon-o-document-magnifying-glass class="w-16 h-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Sin Cotizaciones para Comparar</h3>
                <p class="text-gray-500">Asegúrate de haber filtrado correctamente la solicitud.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
