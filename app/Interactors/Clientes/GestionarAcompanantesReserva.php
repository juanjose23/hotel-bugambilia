<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use App\Repository\Persistencia\Reservas\AcompanantesReservaRepositorio;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class GestionarAcompanantesReserva
{
    public function __construct(
        private ReservaRepositorioInterface $reservaRepositorio,
        private AcompanantesReservaRepositorio $personas,
        private ObtenerDetalleReservaPortal $detallePortal,
    ) {}

    /**
     * @param  array<int, array{nombre: string, identificacion?: string|null, tipo?: string|null, huesped_id?: int|null, reserva_detalle_id?: int|null}>  $acompanantes
     * @return array<string, mixed>
     */
    public function ejecutar(int $reservaId, array $acompanantes, ?User $user = null): array
    {
        if ($user === null) {
            throw new DomainException('Debe iniciar sesion para modificar los acompanantes.');
        }
        $this->detallePortal->ejecutar($reservaId, $user);

        return DB::transaction(function () use ($reservaId, $acompanantes, $user): array {
            /** @var Reserva|null $reserva */
            $reserva = $this->reservaRepositorio->obtenerPorId($reservaId);
            if ($reserva === null) {
                throw new DomainException("La reserva #{$reservaId} no existe.");
            }

            $reserva = $this->personas->bloquear($reserva);
            $clienteId = $user->persona?->cliente?->id;
            $propietario = ($clienteId !== null && $clienteId === $reserva->cliente_id)
                || (is_string($user->email) && strcasecmp($user->email, (string) $reserva->email_cliente) === 0);
            if (! $propietario && ! $user->is_admin && ! $user->can('Update:Reserva')) {
                throw new DomainException('No tienes permiso para modificar los acompanantes de esta reserva.');
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
                    'huesped_id' => $ac['huesped_id'] ?? null,
                    'reserva_detalle_id' => $ac['reserva_detalle_id'] ?? null,
                    'nombre' => $nombre,
                    'identificacion' => isset($ac['identificacion']) ? trim($ac['identificacion']) : '',
                    'tipo' => $tipo,
                ];
            }

            $this->personas->reemplazar($reserva, $acompanantesLimpios);

            $reserva->actualizarOCrearEntradaBitacora('actualizacion_acompanantes', [
                'cantidad' => count($acompanantesLimpios),
                'actualizado_at' => now()->toIso8601String(),
                'usuario_id' => $user->id,
            ]);

            return [
                'reserva_id' => $reserva->id,
                'acompanantes' => $acompanantesLimpios,
            ];
        });
    }
}
