<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaBitacora;
use App\Repository\Models\User;
use DomainException;

final class RegistrarValoracionEstancia
{
    /**
     * @return array<string, mixed>
     */
    public function ejecutar(int $reservaId, int $estrellas, ?string $comentario = null, ?User $user = null, ?string $codigo = null): array
    {
        /** @var Reserva|null $reserva */
        $reserva = Reserva::find($reservaId);

        if ($reserva === null) {
            throw new DomainException("La reserva #{$reservaId} no existe.");
        }

        $esValidoCodigo = $codigo !== null && trim($codigo) !== '' && $reserva->codigo_reserva === trim($codigo);
        $esAdmin = $user !== null && ($user->is_admin || $user->can('View:Reserva'));
        $esPropietario = $user !== null && (
            ($user->persona?->cliente?->id !== null && $user->persona->cliente->id === $reserva->cliente_id)
            || (is_string($user->email) && strcasecmp($user->email, (string) $reserva->email_cliente) === 0)
        );

        if (! $esValidoCodigo && ! $esAdmin && ! $esPropietario) {
            throw new DomainException('No tienes autorización para valorar esta reservación.');
        }

        $bitacora = ReservaBitacora::create([
            'reserva_id' => $reserva->id,
            'tipo' => 'valoracion_estancia',
            'datos' => [
                'estrellas' => $estrellas,
                'comentario' => $comentario,
                'valorado_por' => $user !== null ? $user->name : $reserva->nombre_cliente,
                'fecha' => now()->toIso8601String(),
            ],
        ]);

        return [
            'id' => $bitacora->id,
            'estrellas' => $estrellas,
            'comentario' => $comentario,
        ];
    }
}
