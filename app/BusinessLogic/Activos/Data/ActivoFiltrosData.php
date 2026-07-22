<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos\Data;

final readonly class ActivoFiltrosData
{
    public function __construct(
        public ?int $activoId,
        public ?int $productoId,
        public ?int $estado,
        public ?string $ubicacionTipo,
        public ?int $tipoPagina,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            activoId: self::toInt($input['activo_id'] ?? null),
            productoId: self::toInt($input['producto_id'] ?? null),
            estado: self::toInt($input['estado'] ?? null),
            ubicacionTipo: is_string($input['ubicacion_tipo'] ?? null) ? $input['ubicacion_tipo'] : null,
            tipoPagina: self::toInt($input['tipo_pagina'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'activo_id' => $this->activoId,
            'producto_id' => $this->productoId,
            'estado' => $this->estado,
            'ubicacion_tipo' => $this->ubicacionTipo,
            'tipo_pagina' => $this->tipoPagina,
        ];
    }

    private static function toInt(mixed $value): ?int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        return $value === false ? null : $value;
    }
}
