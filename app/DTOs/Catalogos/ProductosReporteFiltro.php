<?php

declare(strict_types=1);

namespace App\DTOs\Catalogos;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class ProductosReporteFiltro
{
    public function __construct(
        public ?int $categoriaId = null,
        public ?int $marcaId = null,
        public ?int $tipo = null,
        public ?int $estado = null,
        public ?int $id = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public static function fromArray(array $data): self
    {
        $validated = Validator::make($data, [
            'categoria_id' => 'nullable|integer',
            'marca_id' => 'nullable|integer',
            'tipo' => 'nullable|integer',
            'estado' => 'nullable|integer',
            'id' => 'nullable|integer',
        ])->validate();

        return new self(
            categoriaId: isset($validated['categoria_id']) ? (int) $validated['categoria_id'] : null,
            marcaId: isset($validated['marca_id']) ? (int) $validated['marca_id'] : null,
            tipo: isset($validated['tipo']) ? (int) $validated['tipo'] : null,
            estado: isset($validated['estado']) ? (int) $validated['estado'] : null,
            id: isset($validated['id']) ? (int) $validated['id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'categoria_id' => $this->categoriaId,
            'marca_id' => $this->marcaId,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'id' => $this->id,
        ];
    }
}
