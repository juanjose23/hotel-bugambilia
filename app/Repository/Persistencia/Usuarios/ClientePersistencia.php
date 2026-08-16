<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;

final class ClientePersistencia
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearDesdePersona(Persona $persona, array $datos): Cliente
    {
        return Cliente::create([
            'persona_id' => $persona->id,
            'catalogo_id' => $datos['catalogo_id'],
            'estado' => EstadoGeneral::Activo,
        ]);
    }

    /**
     * Reutiliza el cliente existente de la persona o crea uno nuevo.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crearORecuperarDesdePersona(Persona $persona, array $datos): Cliente
    {
        $cliente = $persona->cliente;

        if ($cliente instanceof Cliente) {
            return $cliente;
        }

        return $this->crearDesdePersona($persona, $datos);
    }
}
