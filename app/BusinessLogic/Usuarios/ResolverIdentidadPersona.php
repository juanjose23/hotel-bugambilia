<?php

declare(strict_types=1);

namespace App\BusinessLogic\Usuarios;

use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\User;

final class ResolverIdentidadPersona
{
    public function __construct(
        private readonly CompararDatosPersona $comparador,
    ) {}

    /**
     * Resuelve la identidad de una persona según su identificación y correo.
     *
     * @param  array<string, mixed>  $datos
     * @return array{tipo: string, persona: Persona|null, tipo_conflicto: TipoConflictoIdentidad|null}
     */
    public function resolver(array $datos): array
    {
        $persona = $this->buscarPorIdentificacion($datos)
            ?? $this->buscarPorEmail($datos);

        if ($persona === null) {
            return ['tipo' => 'crear_nueva', 'persona' => null, 'tipo_conflicto' => null];
        }

        return $this->evaluarPersonaEncontrada($persona, $datos);
    }

    // -------------------------------------------------------------------------
    // Evaluación de la persona encontrada
    // -------------------------------------------------------------------------

    /**
     * Determina qué acción tomar según el rol/estado de la persona encontrada.
     *
     * @param  array<string, mixed>  $datos
     * @return array{tipo: string, persona: Persona|null, tipo_conflicto: TipoConflictoIdentidad|null}
     *
     * @throws YaTieneCuentaException
     */
    private function evaluarPersonaEncontrada(Persona $persona, array $datos): array
    {
        $esColaborador = $persona->colaborador()->exists();
        $esProveedor = $persona->proveedor()->exists();

        if ($this->yaEstaRegistradaComoCliente($persona, $esColaborador, $esProveedor)) {
            throw new YaTieneCuentaException(
                'Esta cuenta ya está registrada como cliente. Por favor, inicie sesión.',
                $persona
            );
        }

        if ($esColaborador || $esProveedor) {
            return ['tipo' => 'vincular_directo', 'persona' => $persona, 'tipo_conflicto' => null];
        }

        $comparacion = $this->comparador->comparar($persona, $datos);

        return [
            'tipo' => $comparacion['tipo'],
            'persona' => $persona,
            'tipo_conflicto' => $comparacion['tipo_conflicto'],
        ];
    }

    private function yaEstaRegistradaComoCliente(Persona $persona, bool $esColaborador, bool $esProveedor): bool
    {
        return $persona->cliente()->exists()
            || ($persona->user !== null && ! $esColaborador && ! $esProveedor);
    }

    // -------------------------------------------------------------------------
    // Búsquedas en BD
    // -------------------------------------------------------------------------

    /**
     * Busca por tipo + número de identificación directamente en BD.
     *
     * @param  array<string, mixed>  $datos
     */
    private function buscarPorIdentificacion(array $datos): ?Persona
    {
        $tipo = $datos['tipo_identificacion'] ?? null;
        $numero = $datos['numero_identificacion'] ?? null;

        if (! is_string($tipo) || ! is_string($numero) || trim($numero) === '') {
            return null;
        }

        $tipoNorm = $this->normalizar($tipo);
        $numeroNorm = $this->normalizar($numero);

        $pn = PersonaNatural::with('persona')
            ->whereRaw("LOWER(REPLACE(REPLACE(tipo_identificacion, '-', ''), ' ', '')) = ?", [$tipoNorm])
            ->whereRaw("LOWER(REPLACE(REPLACE(numero_identificacion, '-', ''), ' ', '')) = ?", [$numeroNorm])
            ->first();

        return $pn?->persona;
    }

    /**
     * Busca por correo electrónico a través de la cuenta de usuario.
     *
     * @param  array<string, mixed>  $datos
     */
    private function buscarPorEmail(array $datos): ?Persona
    {
        $email = $datos['email'] ?? null;

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        $user = User::where('email', trim($email))->first();

        return $user?->persona;
    }

    private function normalizar(string $valor): string
    {
        return strtolower((string) preg_replace('/[^a-z0-9]/i', '', $valor));
    }
}
