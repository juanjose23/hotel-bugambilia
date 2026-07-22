<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Reportes;

final readonly class SolicitudesEstadoReporteData
{
    /**
     * @param  array<string, int>  $data
     */
    public function __construct(
        public array $data,
        public string $fechaInicio,
        public string $fechaFin,
        public ?string $estado = null,
    ) {}

    /**
     * @return array{data: array<string, int>, fechaInicio: string, fechaFin: string, estado: string|null}
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'estado' => $this->estado,
        ];
    }
}
