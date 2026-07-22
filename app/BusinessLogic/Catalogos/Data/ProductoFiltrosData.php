<?php

declare(strict_types=1);

namespace App\BusinessLogic\Catalogos\Data;

final readonly class ProductoFiltrosData
{
    public function __construct(
        public ?int $productoId,
        public ?int $categoriaId,
        public ?int $marcaId,
        public ?int $tipo,
        public ?int $estado,
        public ?string $tipoPagina,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            productoId: self::toInt($input['producto_id'] ?? null),
            categoriaId: self::toInt($input['categoria_id'] ?? null),
            marcaId: self::toInt($input['marca_id'] ?? null),
            tipo: self::toInt($input['tipo'] ?? null),
            estado: self::toInt($input['estado'] ?? null),
            tipoPagina: is_string($input['tipo_pagina'] ?? null) ? $input['tipo_pagina'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'producto_id' => $this->productoId,
            'categoria_id' => $this->categoriaId,
            'marca_id' => $this->marcaId,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'tipo_pagina' => $this->tipoPagina,
        ];
    }

    private static function toInt(mixed $value): ?int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        return $value === false ? null : $value;
    }
}
