<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoRecepcion;
use App\Repository\Models\Compras\OrdenCompraItem;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\RecepcionItem;

interface RecepcionRepositorioInterface
{
    public function actualizarEstado(RecepcionCompra $recepcion, EstadoRecepcion $estado): void;

    public function buscarItemPorId(int $id): ?RecepcionItem;

    public function buscarOrdenCompraItemConSumaRecepcion(int $id): ?OrdenCompraItem;

    public function obtenerUltimoCodigoPorAno(int $year): ?string;
}
