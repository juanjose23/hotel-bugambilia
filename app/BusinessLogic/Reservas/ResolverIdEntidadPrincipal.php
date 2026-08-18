<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\TipoReserva;

/**
 * Determina el ID de la entidad principal de la reserva según el tipo y el payload.
 *
 * Cada TipoReserva mapea a un campo específico del payload:
 * - HABITACION  → habitacion_id
 * - RESTAURANTE → espacio_id
 * - SERVICIO    → servicio_id
 * - PAQUETE     → habitacion_id | espacio_id | servicio_id | paquete_id (fallback)
 *
 * Delega la extracción de valores numéricos a {@see LeerDatoReserva}
 * para evitar duplicación de lógica de parsing.
 */
final readonly class ResolverIdEntidadPrincipal
{
    public function __construct(
        private LeerDatoReserva $leerDato,
    ) {}

    /**
     * Resuelve el ID de la entidad principal asociada al tipo de reserva.
     *
     * @param  array<string, mixed>  $datos  Payload de la reserva (campos habitacion_id, espacio_id, etc.)
     *
     * @throws \InvalidArgumentException si el campo requerido para el tipo no es numérico válido
     */
    public function resolver(TipoReserva $tipo, array $datos): int
    {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->leerDato->enteroRequerido($datos, 'habitacion_id'),
            TipoReserva::RESTAURANTE => $this->leerDato->enteroRequerido($datos, 'espacio_id'),
            TipoReserva::SERVICIO => $this->leerDato->enteroRequerido($datos, 'servicio_id'),
            TipoReserva::PAQUETE => $this->resolverPaquete($datos),
        };
    }

    /**
     * Para paquetes, intenta resolver en orden de prioridad:
     * habitación → espacio → servicio → paquete_id (fallback con default 0).
     *
     * @param  array<string, mixed>  $datos
     */
    private function resolverPaquete(array $datos): int
    {
        if (is_numeric($datos['habitacion_id'] ?? null)) {
            return (int) $datos['habitacion_id'];
        }

        if (is_numeric($datos['espacio_id'] ?? null)) {
            return (int) $datos['espacio_id'];
        }

        if (is_numeric($datos['servicio_id'] ?? null)) {
            return (int) $datos['servicio_id'];
        }

        return $this->leerDato->enteroOpcional($datos, 'paquete_id', 0);
    }
}
