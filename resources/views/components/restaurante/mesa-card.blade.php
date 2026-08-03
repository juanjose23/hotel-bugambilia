@props([
    'mesa',
    'estilo' => [],
    'simboloMoneda' => 'C$',
    'estados' => [],
])

@php
    $capacidad = (int) ($mesa->capacidad_personas ?? 0);

    $estadoActual = is_object($mesa->estado)
        ? (int) $mesa->estado->value
        : (int) $mesa->estado;

    $estadoActualEnum = collect($estados)->first(
        static fn ($estado): bool =>
            is_object($estado)
            && isset($estado->value)
            && (int) $estado->value === $estadoActual
    );

    $rawMeta = match (true) {
        is_array($mesa->meta_datos) => $mesa->meta_datos,

        is_string($mesa->meta_datos)
            && trim($mesa->meta_datos) !== ''
            => json_decode($mesa->meta_datos, true),

        default => [],
    };

    $meta = is_array($rawMeta)
        ? $rawMeta
        : [];

    $esSecundariaUnida = ! empty(
        $meta['mesa_principal_id'] ?? null
    );

    $mesasUnidas = is_array($meta['mesas_unidas'] ?? null)
        ? $meta['mesas_unidas']
        : [];

    $esPrincipalConUnidas = $mesasUnidas !== [];

    $motivoUnion = $meta['motivo_union']
        ?? 'uso_inmediato';

    $codigoReserva = $meta['codigo_reserva']
        ?? null;

    $tipoMesa = strtolower(
        trim((string) ($meta['tipo_mesa'] ?? 'redonda'))
    );

    $tiposMesa = [
        'redonda' => [
            'label' => 'Redonda',
            'descripcion' => 'Mesa circular',
            'icono' => 'hugeicons-restaurant-table',
            'forma' => ['h-28', 'w-28', 'rounded-full'],
            'iconoColor' => ['text-stone-600', 'dark:text-stone-300'],
            'iconoFondo' => ['bg-stone-100', 'dark:bg-stone-800'],
            'borde' => ['border-stone-200', 'dark:border-stone-700'],
            'etiqueta' => [
                'bg-stone-100', 'text-stone-600', 'ring-stone-200',
                'dark:bg-stone-800', 'dark:text-stone-300', 'dark:ring-stone-700',
            ],
            'resplandor' => ['bg-sky-400/15', 'dark:bg-sky-500/10'],
        ],

        'rectangular' => [
            'label' => 'Rectangular',
            'descripcion' => 'Mesa rectangular',
            'icono' => 'hugeicons-table-02',
            'forma' => ['h-28', 'w-36', 'rounded-2xl'],
            'iconoColor' => ['text-stone-600', 'dark:text-stone-300'],
            'iconoFondo' => ['bg-stone-100', 'dark:bg-stone-800'],
            'borde' => ['border-stone-200', 'dark:border-stone-700'],
            'etiqueta' => [
                'bg-stone-100', 'text-stone-600', 'ring-stone-200',
                'dark:bg-stone-800', 'dark:text-stone-300', 'dark:ring-stone-700',
            ],
            'resplandor' => ['bg-violet-400/15', 'dark:bg-violet-500/10'],
        ],

        'barra' => [
            'label' => 'Barra',
            'descripcion' => 'Área de barra',
            'icono' => 'hugeicons-restaurant-01',
            'forma' => ['h-28', 'w-full', 'max-w-52', 'rounded-2xl'],
            'iconoColor' => ['text-stone-600', 'dark:text-stone-300'],
            'iconoFondo' => ['bg-stone-100', 'dark:bg-stone-800'],
            'borde' => ['border-stone-200', 'dark:border-stone-700'],
            'etiqueta' => [
                'bg-stone-100', 'text-stone-600', 'ring-stone-200',
                'dark:bg-stone-800', 'dark:text-stone-300', 'dark:ring-stone-700',
            ],
            'resplandor' => ['bg-amber-400/15', 'dark:bg-amber-500/10'],
        ],
    ];

    $configuracionTipo = $tiposMesa[$tipoMesa]
        ?? $tiposMesa['redonda'];

    $tienePedidos = $mesa->relationLoaded('pedidosActivos')
        && $mesa->pedidosActivos->isNotEmpty();

    $cantidadPedidos = $tienePedidos
        ? $mesa->pedidosActivos->count()
        : 0;

    $totalMesa = (float) ($mesa->total_mesa ?? 0);
@endphp

<article
    {{ $attributes->class([
        'group relative flex min-w-0 flex-col overflow-hidden',
        'rounded-2xl border bg-white p-4 shadow-xs dark:bg-gray-900',
        'transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5',
        'min-h-[380px]',
        'border-gray-200 hover:border-gray-300 hover:shadow-md dark:border-gray-800 dark:hover:border-gray-700 dark:hover:shadow-black/50',
        'ring-2 ring-primary-500/50 dark:ring-primary-400/50' => $esSecundariaUnida,
    ]) }}
>
    {{-- Indicador superior discreto --}}
    <div
        class="pointer-events-none absolute inset-x-6 top-0 h-0.5 bg-gray-200 dark:bg-gray-800"
    ></div>

    <x-restaurante.mesa-card.header
        :mesa="$mesa"
        :configuracion-tipo="$configuracionTipo"
        :capacidad="$capacidad"
        :estado-actual-enum="$estadoActualEnum"
    />

    <x-restaurante.mesa-card.mesas-unidas
        :mesa="$mesa"
        :es-secundaria-unida="$esSecundariaUnida"
        :es-principal-con-unidas="$esPrincipalConUnidas"
        :meta="$meta"
        :mesas-unidas="$mesasUnidas"
        :motivo-union="$motivoUnion"
        :codigo-reserva="$codigoReserva"
    />

    <x-restaurante.mesa-card.visual
        :mesa="$mesa"
        :configuracion-tipo="$configuracionTipo"
        :capacidad="$capacidad"
        :estilo="$estilo"
        :tiene-pedidos="$tienePedidos"
        :cantidad-pedidos="$cantidadPedidos"
    />

    <x-restaurante.mesa-card.resumen
        :mesa="$mesa"
        :simbolo-monedas="$simboloMoneda"
        :tiene-pedidos="$tienePedidos"
        :cantidad-pedidos="$cantidadPedidos"
        :total-mesa="$totalMesa"
    />

    <x-restaurante.mesa-card.acciones
        :mesa="$mesa"
        :estados="$estados"
        :estado-actual="$estadoActual"
        :estado-actual-enum="$estadoActualEnum"
        :tiene-pedidos="$tienePedidos"
    />
</article>
