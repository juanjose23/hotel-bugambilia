<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Clientes;

use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Persistencia\Usuarios\ClientePersistencia;
use App\Repository\Persistencia\Usuarios\PersonaPersistencia;
use App\Repository\Persistencia\Usuarios\UsuarioCuentaPersistencia;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarClienteNuevo
{
    public function __construct(
        private PersonaPersistencia $personas,
        private ClientePersistencia $clientes,
        private UsuarioCuentaPersistencia $usuarios,
    ) {}

    /**
     * Crea Persona + PersonaNatural/PersonaJuridica + Cliente + [User opcional] en transacción.
     *
     * @param  array<string, mixed>  $datos
     * @return array{persona: Persona, cliente: Cliente, user: User|null}
     */
    public function ejecutar(array $datos, bool $crearUsuario = true): array
    {
        return DB::transaction(function () use ($datos, $crearUsuario): array {
            $persona = $this->personas->crearConIdentidad($datos);
            $cliente = $this->clientes->crearDesdePersona($persona, $datos);

            $user = $crearUsuario ? $this->usuarios->crearCliente($persona, $datos) : null;

            $personaRefrescada = $persona->fresh();

            if (! $personaRefrescada instanceof Persona) {
                throw new \RuntimeException('No se pudo refrescar la persona.');
            }

            return [
                'persona' => $personaRefrescada,
                'cliente' => $cliente,
                'user' => $user,
            ];
        });
    }
}
