<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Clientes;

use App\BusinessLogic\Usuarios\ResolverIdentidadPersona;
use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Events\Usuarios\ClienteRegistrado;
use App\Events\Usuarios\PersonaConflictoIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Interactors\Usuarios\Identidad\ActualizarDatosPersona;
use App\Interactors\Usuarios\Identidad\VincularPersonaExistenteAUser;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final readonly class RegistrarCliente
{
    public function __construct(
        private ResolverIdentidadPersona $resolver,
        private RegistrarClienteNuevo $registrarNuevo,
        private VincularPersonaExistenteAUser $vincularExistente,
    ) {}

    /**
     * Orquestador principal para registrar un cliente.
     *
     * @param  array<string, mixed>  $datos
     * @return array{cliente: Cliente, persona: Persona, user: User, es_nuevo: bool}
     *
     * @throws YaTieneCuentaException|Throwable
     */
    public function ejecutar(array $datos): array
    {
        $resultado = $this->resolver->resolver($datos);

        return match ($resultado['tipo']) {
            'crear_nueva' => $this->crearNuevo($datos),
            'vincular_directo' => $this->vincularDirecto($this->assertPersona($resultado['persona']), $datos),
            'actualizar_contacto' => $this->actualizarYVincular($this->assertPersona($resultado['persona']), $datos),
            'conflicto_identidad' => $this->crearConflicto($this->assertPersona($resultado['persona']), $datos, $resultado['tipo_conflicto']),
            default => throw new InvalidArgumentException("Tipo de resolución desconocido: {$resultado['tipo']}"),
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
     * @param  array<string, mixed>  $datos
     * @return array{cliente: Cliente, persona: Persona, user: User, es_nuevo: bool}
     *
     * @throws Throwable
     */
    private function vincularDirecto(Persona $persona, array $datos): array
    {
        return DB::transaction(function () use ($persona, $datos): array {
            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'catalogo_id' => $datos['catalogo_id'],
                'estado' => 1,
            ]);

            $user = $this->vincularExistente->ejecutar($persona, $datos);

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
     * @throws Throwable
     */
    private function actualizarYVincular(Persona $persona, array $datos): array
    {
        return DB::transaction(function () use ($persona, $datos): array {
            $actualizador = app(ActualizarDatosPersona::class);
            $actualizador->ejecutar($persona, $datos);

            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'catalogo_id' => $datos['catalogo_id'],
                'estado' => 1,
            ]);

            $user = $this->vincularExistente->ejecutar($persona, $datos);

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
        ?TipoConflictoIdentidad $tipoConflicto
    ): array {
        $conflicto = ConflictoIdentidad::create([
            'persona_id' => $persona->id,
            'tipo_conflicto' => $tipoConflicto ?? TipoConflictoIdentidad::Homonimia,
            'datos_providos' => $datos,
            'datos_existentes' => [
                'nombre' => $persona->nombre_completo,
                'telefono' => $persona->telefono,
                'direccion' => $persona->direccion,
            ],
            'estado' => EstadoConflictoIdentidad::Pendiente,
            'creado_por' => Auth::id(),
        ]);

        PersonaConflictoIdentidad::dispatch(
            $conflicto,
            $persona,
            $tipoConflicto ?? TipoConflictoIdentidad::Homonimia,
            $datos,
            [
                'nombre' => $persona->nombre_completo,
                'telefono' => $persona->telefono,
                'direccion' => $persona->direccion,
            ]
        );

        throw new RuntimeException(
            'Se detectó un conflicto de identidad. Un administrador deberá revisarlo antes de continuar.'
        );
    }
}
