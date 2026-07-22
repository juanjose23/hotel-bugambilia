<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Reportes;

final readonly class TrazabilidadCompletaReporteData
{
    /**
     * @param  array<int, mixed>  $cotizaciones
     * @param  array<int, mixed>  $ordenesCompra
     * @param  array<int, mixed>  $recepciones
     */
    public function __construct(
        public object $solicitud,
        public array $cotizaciones,
        public array $ordenesCompra,
        public array $recepciones,
    ) {}

    /**
     * @return array{solicitud: object, cotizaciones: array<int, mixed>, ordenesCompra: array<int, mixed>, recepciones: array<int, mixed>}
     */
    public function toArray(): array
    {
        return [
            'solicitud' => $this->solicitud,
            'cotizaciones' => $this->cotizaciones,
            'ordenesCompra' => $this->ordenesCompra,
            'recepciones' => $this->recepciones,
        ];
    }
}
