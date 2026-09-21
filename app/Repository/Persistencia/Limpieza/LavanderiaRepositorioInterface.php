<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Limpieza;

use App\Repository\Models\Inventario\Stock;

interface LavanderiaRepositorioInterface
{
    /** @param int|array<int> $ubicacionId */
    public function buscarStockPorIdConLock(int $stockId, int|array $ubicacionId): Stock;

    public function reponerDestinoStock(string $stockableType, int $destinoId, ?int $varianteId, float $cantidad, ?int $loteId): void;

    public function resolverUbicacionDestino(string $stockableType, int $destinoId): ?int;

    public function entradaInsumosDesdeBodega(
        int $bodegaOrigenId,
        int $lavanderiaId,
        int $varianteId,
        int $productoId,
        float $cantidad,
        ?int $loteId,
        ?int $creadoPorId,
        ?string $referencia,
        ?string $notas
    ): void;

    public function entradaDirectaInsumos(
        int $lavanderiaId,
        int $productoId,
        int $varianteId,
        float $cantidad,
        ?string $codigoLote,
        ?float $costoUnitario,
        ?string $fechaVencimiento,
        ?int $creadoPorId,
        ?string $referencia,
        ?string $notas
    ): void;

    public function entradaDirectaPrenda(
        int $lavanderiaId,
        int $productoId,
        int $varianteId,
        float $cantidad,
        ?int $loteId,
        ?string $tipoOrigen,
        ?int $origenId,
        ?int $creadoPorId,
        ?string $notas,
        string $referencia
    ): void;

    /** @param int|array<int> $ubicacionLavanderiaId */
    public function descontarStockJornada(
        int $stockId,
        int|array $ubicacionLavanderiaId,
        float $cantidad,
        string $referencia,
        ?int $creadoPorId,
        ?string $notas,
        string $tipoMovimiento,
        string $documentoTipo = 'jornada_lavanderia'
    ): Stock;

    public function enviarALavanderia(
        int $stockId,
        int $lavanderiaId,
        float $cantidad,
        string $tipo,
        ?int $creadoPorId,
        ?string $notas
    ): void;

    public function buscarNombreTurno(int $turnoId): ?string;
}
