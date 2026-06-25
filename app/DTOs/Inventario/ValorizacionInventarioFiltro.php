<?php

declare(strict_types=1);

namespace App\DTOs\Inventario;

readonly class ValorizacionInventarioFiltro
{
    public function __construct(
        public ?int $ubicacionId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $ubicacionId = $data['ubicacion_id'] ?? null;

        return new self(
            ubicacionId: is_numeric($ubicacionId) ? (int) $ubicacionId : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ubicacion_id' => $this->ubicacionId,
        ];
    }
}
