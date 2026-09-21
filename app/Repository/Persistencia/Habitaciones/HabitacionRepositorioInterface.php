<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Habitaciones\Habitacion;

interface HabitacionRepositorioInterface
{
    public function existePorSlug(string $slug, ?int $idAIgnorar = null): bool;

    public function existePorNumero(int $numero, ?int $idAIgnorar = null): bool;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Habitacion;

    public function buscarPorId(int $id): ?Habitacion;

    public function buscarPorIdConLock(int $id): Habitacion;

    public function buscarPorRecursoReservableId(int $recursoReservableId): ?Habitacion;

    public function buscarPorRecursoReservableIdConLock(int $recursoReservableId): ?Habitacion;

    public function actualizarEstado(Habitacion $habitacion, EstadoEspacio $estado): void;

    /** @param array<array-key, mixed> $imagenes */
    public function sincronizarImagenes(Habitacion $habitacion, array $imagenes): void;

    public function clonar(
        Habitacion $origen,
        int $nuevoNumero,
        ?string $nuevoNombre = null,
        ?string $nuevoSlug = null,
        ?string $nuevoCodigo = null,
    ): Habitacion;

    public function generarCodigo(): string;
}
