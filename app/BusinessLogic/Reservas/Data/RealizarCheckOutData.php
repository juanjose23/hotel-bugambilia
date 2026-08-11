<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class RealizarCheckOutData
{
    public function __construct(
        public int $estanciaId,
        public ?float $cargosFinales = null,
        public ?float $pagosAdicionales = null,
        public ?string $observaciones = null,
        public bool $autorizarSaldoPendiente = false,
        public int $llavesDevueltas = 1,
        public bool $autorizarLlavesPendientes = false,
        public ?int $usuarioId = null,
    ) {}
}
