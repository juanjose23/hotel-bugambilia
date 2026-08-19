@php
    $paginasList = [];
    if (isset($paginas) && (is_array($paginas) || $paginas instanceof \Illuminate\Support\Collection)) {
        foreach ($paginas as $chunk) {
            if ($chunk instanceof \Illuminate\Support\Collection && $chunk->isNotEmpty()) {
                $paginasList[] = $chunk;
            } elseif (is_array($chunk) && !empty($chunk)) {
                $paginasList[] = collect($chunk);
            }
        }
    } elseif (isset($items)) {
        $itemsCol = is_array($items) ? collect($items) : $items;
        if ($itemsCol->isNotEmpty()) {
            $paginasList[] = $itemsCol;
        }
    }
    if (empty($paginasList)) {
        $paginasList[] = collect();
    }
@endphp

<div class="report-content">
    @foreach($paginasList as $i => $itemsChunk)
        <div class="pagina">
            @if($i > 0)
                <div class="page-top-spacer"></div>
            @endif

            @if($itemsChunk->isNotEmpty())
                @include($tableView, array_merge($tableData ?? [], [
                    'items' => $itemsChunk,
                    'paginaIndex' => $i,
                    'esUltimaPagina' => $loop->last,
                ]))
            @endif
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    @if(isset($alertMessage))
        <div style="margin-top:12px; font-size:8pt; color:#92400e; background:#fffbeb; border:1px solid #fde68a; padding:8px 12px; border-radius:4px;" class="avoid-break">
            {{ $alertMessage }}
        </div>
    @endif
</div>
