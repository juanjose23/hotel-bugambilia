<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class AutenticarClientePorCodigoReserva
{
    /**
     * Autentica automáticamente al cliente creando una sesión normal de usuario
     * a partir de su código de reserva válido.
     */
    public function ejecutar(string $codigo): ?User
    {
        $codigoLimpio = trim($codigo);
        if ($codigoLimpio === '') {
            return null;
        }

        /** @var Reserva|null $reserva */
        $reserva = Reserva::with(['cliente.persona.user'])
            ->where('codigo_reserva', $codigoLimpio)
            ->first();

        if ($reserva === null) {
            return null;
        }

        // 1. Si ya existe un User para el cliente asociado a la reserva
        $user = $reserva->cliente?->persona?->user;

        // 2. Si no, buscar por el email del cliente
        if (! $user instanceof User && ! empty($reserva->email_cliente)) {
            $user = User::where('email', trim($reserva->email_cliente))->first();
        }

        // 3. Si no existe usuario, crearlo automáticamente para brindarle cuenta completa
        if (! $user instanceof User) {
            $user = DB::transaction(function () use ($reserva): User {
                $email = ! empty($reserva->email_cliente)
                    ? trim($reserva->email_cliente)
                    : "huesped_{$reserva->codigo_reserva}@hotelbugambilias.com";

                $persona = $reserva->cliente?->persona;

                if (! $persona instanceof Persona) {
                    $persona = Persona::create([
                        'tipo' => 'NATURAL',
                        'email' => $email,
                        'telefono' => $reserva->telefono_cliente,
                    ]);

                    PersonaNatural::create([
                        'persona_id' => $persona->id,
                        'primer_nombre' => $reserva->nombre_cliente ?: 'Huésped',
                        'primer_apellido' => 'Bugambilias',
                    ]);

                    $cliente = Cliente::create([
                        'persona_id' => $persona->id,
                        'categoria' => 'ESTANDAR',
                        'estado' => true,
                    ]);

                    $reserva->update(['cliente_id' => $cliente->id]);
                }

                return User::create([
                    'persona_id' => $persona->id,
                    'name' => $reserva->nombre_cliente ?: 'Huésped Hotel Bugambilias',
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'is_admin' => false,
                ]);
            });
        }

        // 4. Iniciar sesión de usuario normal
        Auth::login($user, true);

        return $user;
    }
}
