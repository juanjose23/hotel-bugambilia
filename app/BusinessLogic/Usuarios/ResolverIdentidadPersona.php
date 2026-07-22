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
        $tipoIdentificacion = $datos['tipo_identificacion'] ?? null;
        $numeroIdentificacion = $datos['numero_identificacion'] ?? null;
        $email = $datos['email'] ?? null;

        $persona = null;

        // 1. Buscar por identificación si se proporcionó y no está vacía
        if (is_string($tipoIdentificacion) && is_string($numeroIdentificacion) && trim($numeroIdentificacion) !== '') {
            $tipoNorm = $this->normalizar($tipoIdentificacion);
            $numNorm = $this->normalizar($numeroIdentificacion);

            $pn = PersonaNatural::with('persona')
                ->get()
                ->first(fn (PersonaNatural $p) => $this->normalizar($p->tipo_identificacion ?? '') === $tipoNorm
                    && $this->normalizar($p->numero_identificacion ?? '') === $numNorm);

            if ($pn instanceof PersonaNatural) {
                $persona = $pn->persona;
            }
        }

        // 2. Si no se encontró, buscar por correo electrónico a través de la cuenta de usuario
        if ($persona === null && is_string($email) && trim($email) !== '') {
            $user = User::where('email', trim($email))->first();
            if ($user && $user->persona) {
                $persona = $user->persona;
            }
        }

        // 3. Si no existe ningún registro, crear una persona nueva
        if ($persona === null) {
            return [
                'tipo' => 'crear_nueva',
                'persona' => null,
                'tipo_conflicto' => null,
            ];
        }

        // 4. Si la persona existe:
        $esColaborador = $persona->colaborador()->exists();
        $esProveedor = $persona->proveedor()->exists();

        // REGLA: Si ya es un cliente (tiene cuenta vinculada) o tiene usuario y NO es colaborador/proveedor, no se permite registrar de nuevo
        if ($persona->cliente()->exists() || ($persona->user !== null && ! $esColaborador && ! $esProveedor)) {
            throw new YaTieneCuentaException(
                'Esta cuenta ya está registrada como cliente. Por favor, inicie sesión.',
                $persona
            );
        }

        // REGLA: Si es colaborador o proveedor, se le deja pasar vinculando la cuenta directamente
        if ($esColaborador || $esProveedor) {
            return [
                'tipo' => 'vincular_directo',
                'persona' => $persona,
                'tipo_conflicto' => null,
            ];
        }

        // Para cualquier otra persona (ej. huésped registrado físicamente sin usuario), realizar comparación normal
        $comparacion = $this->comparador->comparar($persona, $datos);

        return [
            'tipo' => $comparacion['tipo'],
            'persona' => $persona,
            'tipo_conflicto' => $comparacion['tipo_conflicto'],
        ];
    }

    private function normalizar(string $valor): string
    {
        return strtolower((string) preg_replace('/[^a-z0-9]/i', '', $valor));
    }
}
