<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Identidad;

use App\BusinessLogic\Usuarios\SincronizarContactoPersona;
use App\Exceptions\YaTieneCuentaException;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Persistencia\Usuarios\PersonaPersistencia;
use App\Repository\Persistencia\Usuarios\UsuarioCuentaPersistencia;
use App\Repository\Queries\Usuarios\BuscarUsuarioCuentaQuery;
use Illuminate\Support\Facades\DB;

final readonly class VincularPersonaAUser
{
    public function __construct(
        private BuscarUsuarioCuentaQuery $usuarios,
        private UsuarioCuentaPersistencia $persistencia,
        private SincronizarContactoPersona $sincronizarContacto,
        private PersonaPersistencia $personas,
    ) {}

    /**
     * Vincula una Persona existente a su cuenta de usuario, reutilizándola si ya existe.
     *
     * @param  array<string, mixed>  $datos
     *
     * @throws YaTieneCuentaException Cuando el correo provisto pertenece a otra persona.
     */
    public function ejecutar(Persona $persona, array $datos): User
    {
        return DB::transaction(function () use ($persona, $datos): User {
            $this->aplicarCambiosDeContacto($persona, $datos);

            $usuarioPorPersona = $this->usuarios->porPersona($persona);

            if ($usuarioPorPersona instanceof User) {
                $this->persistencia->actualizarPasswordSiFueProvista($usuarioPorPersona, $datos);

                return $usuarioPorPersona;
            }

            $this->verificarEmailLibre($persona, $datos);

            return $this->persistencia->crearCliente($persona, $datos);
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function aplicarCambiosDeContacto(Persona $persona, array $datos): void
    {
        $cambios = $this->sincronizarContacto->decidirCambios($persona, $datos);

        if ($cambios !== []) {
            $this->personas->actualizarDatosBasicos($persona, $cambios);
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     *
     * @throws YaTieneCuentaException
     */
    private function verificarEmailLibre(Persona $persona, array $datos): void
    {
        $email = $datos['email'] ?? null;

        if (! is_string($email) || trim($email) === '') {
            return;
        }

        $usuarioPorEmail = $this->usuarios->porEmail($email);

        if (! $usuarioPorEmail instanceof User) {
            return;
        }

        $duenoDelEmail = $usuarioPorEmail->persona;

        throw new YaTieneCuentaException(
            'El correo electrónico ya está vinculado a otra cuenta.',
            $duenoDelEmail ?? $persona,
        );
    }
}
