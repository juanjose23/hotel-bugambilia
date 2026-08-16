<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Clientes;

use App\BusinessLogic\Usuarios\ResolverIdentidadPersona;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Enums\Usuarios\TipoResolucionIdentidad;
use App\Events\Usuarios\ClienteRegistrado;
use App\Events\Usuarios\PersonaConflictoIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Interactors\Usuarios\Identidad\VincularPersonaAUser;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Persistencia\Usuarios\ClientePersistencia;
use App\Repository\Persistencia\Usuarios\ConflictoIdentidadPersistencia;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class RegistrarCliente
{
    public function __construct(
        private ResolverIdentidadPersona $resolver,
        private RegistrarClienteNuevo $registrarNuevo,
        private VincularPersonaAUser $vincularAUser,
        private ClientePersistencia $clientes,
        private ConflictoIdentidadPersistencia $conflictos,
    ) {}

    /**
     * Orquestador principal para registrar un cliente.
     *
     * @param  array<string, mixed>  $datos
     * @param  int|null  $usuarioId  Usuario autenticado que ejecuta la acción (auditoría).
     * @return array{cliente: Cliente, persona: Persona, user: User, es_nuevo: bool}
     *
     * @throws YaTieneCuentaException|RuntimeException
     */
    public function ejecutar(array $datos, ?int $usuarioId = null): array
    {
        $resultado = $this->resolver->resolver($datos);

        return match ($resultado['tipo']) {
            TipoResolucionIdentidad::CrearNueva => $this->crearNuevo($datos),
            TipoResolucionIdentidad::VincularDirecto,
            TipoResolucionIdentidad::ActualizarContacto => $this->vincularPersona(
                $this->assertPersona($resultado['persona']),
                $datos,
            ),
            TipoResolucionIdentidad::ConflictoIdentidad => $this->crearConflicto(
                $this->assertPersona($resultado['persona']),
                $datos,
                $resultado['tipo_conflicto'],
                $usuarioId,
            ),
        };
    }

    private function assertPersona(?Persona $persona): Persona
    {
        if (! $persona instanceof Persona) {
            throw new RuntimeException('Se esperaba una persona existente.');
        }

        return $persona;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{cliente: Cliente, persona: Persona, user: User, es_nuevo: bool}
     */
    private function crearNuevo(array $datos): array
    {
        $resultado = $this->registrarNuevo->ejecutar($datos);

        if (! $resultado['user'] instanceof User) {
            throw new RuntimeException('No se pudo crear el usuario para el nuevo cliente.');
        }

        ClienteRegistrado::dispatch($resultado['cliente'], $resultado['persona'], true);

        return [
            'cliente' => $resultado['cliente'],
            'persona' => $resultado['persona'],
            'user' => $resultado['user'],
            'es_nuevo' => true,
        ];
    }

    /**
     * Vincula una persona existente reutilizando su cliente y cuenta de usuario.
     *
     * @param  array<string, mixed>  $datos
     * @return array{cliente: Cliente, persona: Persona, user: User, es_nuevo: bool}
     *
     * @throws Throwable
     */
    private function vincularPersona(Persona $persona, array $datos): array
    {
        return DB::transaction(function () use ($persona, $datos): array {
            $cliente = $this->clientes->crearORecuperarDesdePersona($persona, $datos);

            $user = $this->vincularAUser->ejecutar($persona, $datos);

            ClienteRegistrado::dispatch($cliente, $persona, false);

            $refrescada = $persona->fresh();

            if (! $refrescada instanceof Persona) {
                throw new RuntimeException('No se pudo refrescar la persona.');
            }

            return [
                'cliente' => $cliente,
                'persona' => $refrescada,
                'user' => $user,
                'es_nuevo' => false,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{cliente: Cliente, persona: Persona, user: User, es_nuevo: bool}
     *
     * @throws RuntimeException
     */
    private function crearConflicto(
        Persona $persona,
        array $datos,
        ?TipoConflictoIdentidad $tipoConflicto,
        ?int $usuarioId,
    ): array {
        $tipoResuelto = $tipoConflicto ?? TipoConflictoIdentidad::Homonimia;
        $datosExistentes = $this->datosExistentesPersona($persona);

        $conflicto = $this->conflictos->crearPendiente(
            $persona,
            $tipoResuelto,
            $datos,
            $datosExistentes,
            $usuarioId,
        );

        PersonaConflictoIdentidad::dispatch(
            $conflicto,
            $persona,
            $tipoResuelto,
            $datos,
            $datosExistentes,
        );

        throw new RuntimeException(
            'Se detectó un conflicto de identidad. Un administrador deberá revisarlo antes de continuar.'
        );
    }

    /**
     * @return array{nombre: string|null, telefono: string|null, direccion: string|null}
     */
    private function datosExistentesPersona(Persona $persona): array
    {
        return [
            'nombre' => $persona->nombre_completo,
            'telefono' => $persona->telefono,
            'direccion' => $persona->direccion,
        ];
    }
}
