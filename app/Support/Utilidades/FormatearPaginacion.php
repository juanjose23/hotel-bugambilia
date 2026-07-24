<?php

declare(strict_types=1);

namespace App\Support\Utilidades;

use Illuminate\Pagination\LengthAwarePaginator;

final class FormatearPaginacion
{
    /**
     * @template T
     *
     * @param  LengthAwarePaginator<int, T>  $paginator
     * @return array{
     *     current_page:int,
     *     last_page:int,
     *     per_page:int,
     *     total:int,
     *     next_page_url:?string,
     *     prev_page_url:?string,
     *     first_page_url:string,
     *     last_page_url:string,
     *     links:array<int, array<string, mixed>>
     * }
     */
    public static function ejecutar(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'first_page_url' => $paginator->url(1),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            /** @var array<int, array<string, mixed>> $links */
            'links' => array_values(array_map(static fn ($link): array => (array) $link, $paginator->linkCollection()->toArray())),
        ];
    }
}
