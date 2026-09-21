<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Usuarios\ClientePersistencia;
use App\Repository\Persistencia\Usuarios\PersonaPersistencia;
use App\Repository\Persistencia\Usuarios\UsuarioCuentaPersistencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class AutenticarClientePorCodigoReserva
{
    public function __construct(
        private ClientePersistencia $clientePersistencia,
        private PersonaPersistencia $personaPersistencia,
        private UsuarioCuentaPersistencia $usuarioPersistencia,
        private ReservaRepositorioInterface $reservaRepositorio,
    ) {}

    /**
     * Autentica automáticamente al cliente creando una sesión normal de usuario
     * a partir de su código de reserva válido.
     *
     * @return array{user: User, reserva: Reserva}|null
     */
    public function ejecutar(string $codigo): ?array
    {
        $codigoLimpio = trim($codigo);
        if ($codigoLimpio === '') {
            return null;
        }

        $reserva = $this->reservaRepositorio->buscarPorCodigoReserva($codigoLimpio);

        if ($reserva === null) {
            return null;
        }

        // 1. Si ya existe un User para el cliente asociado a la reserva
        $user = $reserva->cliente?->persona?->user;

        // 2. Si no, buscar por el email del cliente
        if (! $user instanceof User && ! empty($reserva->email_cliente)) {
            $user = $this->usuarioPersistencia->buscarPorEmail(trim($reserva->email_cliente));
        }

        // 3. Si no existe usuario, crearlo automáticamente para brindarle cuenta completa
        if (! $user instanceof User) {
            $user = DB::transaction(function () use ($reserva): User {
                $email = ! empty($reserva->email_cliente)
                    ? trim($reserva->email_cliente)
                    : "huesped_{$reserva->codigo_reserva}@hotelbugambilias.com";

                $persona = $reserva->cliente?->persona;

                if (! $persona instanceof Persona) {
                    $nombreCompleto = trim((string) ($reserva->nombre_cliente ?: 'Huésped'));
                    $partes = explode(' ', $nombreCompleto, 2);
                    $primerNombre = $partes[0] !== '' ? $partes[0] : 'Huésped';
                    $primerApellido = isset($partes[1]) && $partes[1] !== '' ? $partes[1] : 'Bugambilias';

                    $persona = $this->personaPersistencia->crearConIdentidad([
                        'primer_nombre' => $primerNombre,
                        'primer_apellido' => $primerApellido,
                        'tipo_persona' => 'natural',
                        'telefono' => $reserva->telefono_cliente,
                    ]);

                    $cliente = $this->clientePersistencia->crearDesdePersona($persona, []);

                    $this->reservaRepositorio->actualizar($reserva, ['cliente_id' => $cliente->id]);
                }

                return $this->usuarioPersistencia->crearCliente($persona, [
                    'name' => $reserva->nombre_cliente ?: 'Huésped Hotel Bugambilias',
                    'email' => $email,
                ]);
            });
        }

        // 4. Iniciar sesión del usuario
        Auth::login($user, true);

        return [
            'user' => $user,
            'reserva' => $reserva,
        ];
    }
}
