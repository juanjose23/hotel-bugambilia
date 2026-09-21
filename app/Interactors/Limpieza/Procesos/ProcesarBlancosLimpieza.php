<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final readonly class ProcesarBlancosLimpieza
{
    public function __construct(
        private ProcesarEnvioBlancos $procesarEnvioBlancos,
        private ProcesarReposicionBlancos $procesarReposicionBlancos,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{variante_id: int|null, nombre: string, required: float, available: float}>
     */
    public function execute(LimpiezaEjecucion $ejecucion, array $data, ?int $carritoId, string $tipoDestino, ?int $usuarioId): array
    {
        return $this->ejecutar($ejecucion, $data, $carritoId, $tipoDestino, $usuarioId);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{variante_id: int|null, nombre: string, required: float, available: float}>
     */
    public function ejecutar(LimpiezaEjecucion $ejecucion, array $data, ?int $carritoId, string $tipoDestino, ?int $usuarioId): array
    {
        /** @var array<int|string, int|float|string> $blancosEnviar */
        $blancosEnviar = $data['blancos_enviar'] ?? [];
        $this->procesarEnvioBlancos->procesar($blancosEnviar, $tipoDestino, $usuarioId, (int) $ejecucion->id);

        /** @var array<int|string, int|float|string> $blancosReponer */
        $blancosReponer = $data['blancos_reponer'] ?? [];

        return $this->procesarReposicionBlancos->procesar(
            $blancosReponer,
            $carritoId,
            $tipoDestino,
            (int) $ejecucion->limpiable_id,
            $usuarioId,
            (int) $ejecucion->id,
        );
    }
}
