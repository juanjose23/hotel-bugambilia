<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza\Data;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Database\Eloquent\Model;

final readonly class IniciarLimpiezaData
{
    public function __construct(
        public LimpiezaEjecucion|SolicitudLimpieza|Model $record,
        public ?int $colaboradorOrPersonalId = null,
        public ?int $carritoId = null,
        public ?int $usuarioId = null,
    ) {}
}
