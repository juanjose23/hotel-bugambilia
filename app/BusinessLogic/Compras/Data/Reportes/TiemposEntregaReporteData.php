<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Reportes;

final readonly class TiemposEntregaReporteData
{
    /**
     * @param  array<int, mixed>  $data
     */
    public function __construct(
        public array $data,
        public string $fechaInicio,
        public string $fechaFin,
    ) {}

    /**
     * @return array{data: array<int, mixed>, fechaInicio: string, fechaFin: string}
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ];
    }
}
