<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

class UbicacionNodo
{
    /** @var array<int, UbicacionNodo> */
    public array $children = [];

    /**
     * @param  array<int, UbicacionNodo>  $children
     */
    public function __construct(
        public int $id,
        public string $nombre,
        public ?string $tipo,
        public ?int $padreId,
        array $children = [],
    ) {
        $this->children = $children;
    }
}
