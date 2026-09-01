<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

final class GestionarAcompanantesReserva
{
    /**
     * @param  array<int, array{nombre: string, identificacion?: string|null, tipo?: string|null}>  $acompanantes
     * @return array<string, mixed>
     */
    public function ejecutar(int $reservaId, array $acompanantes, ?User $user = null): array
    {
        return DB::transaction(function () use ($reservaId, $acompanantes, $user): array {
            /** @var Reserva|null $reserva */
            $reserva = Reserva::find($reservaId);
            if ($reserva === null) {
                throw new DomainException("La reserva #{$reservaId} no existe.");
            }

            $acompanantesLimpios = [];
            foreach ($acompanantes as $ac) {
                $nombre = trim($ac['nombre']);
                if ($nombre === '') {
                    continue;
                }

                $tipo = $ac['tipo'] ?? 'adulto';
                if (! in_array($tipo, ['adulto', 'nino', 'bebe'], true)) {
                    $tipo = 'adulto';
                }

                $acompanantesLimpios[] = [
                    'nombre' => $nombre,
                    'identificacion' => isset($ac['identificacion']) ? trim($ac['identificacion']) : '',
                    'tipo' => $tipo,
                ];
            }

            $reserva->update([
                'acompanantes' => $acompanantesLimpios,
            ]);

            $reserva->actualizarOCrearEntradaBitacora('actualizacion_acompanantes', [
                'cantidad' => count($acompanantesLimpios),
                'actualizado_at' => now()->toIso8601String(),
                'usuario_id' => $user?->id,
            ]);

            return [
                'reserva_id' => $reserva->id,
                'acompanantes' => $acompanantesLimpios,
            ];
        });
    }
}
