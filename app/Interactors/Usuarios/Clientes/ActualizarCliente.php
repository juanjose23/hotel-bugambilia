<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Clientes;

use App\Interactors\Usuarios\Identidad\ActualizarDatosPersona;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use Illuminate\Support\Facades\DB;

final class ActualizarCliente
{
    public function __construct(
        private readonly ActualizarDatosPersona $actualizarPersona,
    ) {}

    /**
     * Actualiza datos de un cliente existente.
     *
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(Cliente $cliente, array $datos): Cliente
    {
        return DB::transaction(function () use ($cliente, $datos): Cliente {
            $persona = $cliente->persona;

            if ($persona instanceof Persona) {
                $this->actualizarPersona->ejecutar($persona, $datos);
            }

            if (array_key_exists('catalogo_id', $datos)) {
                $cliente->update(['catalogo_id' => $datos['catalogo_id']]);
            }

            if (array_key_exists('estado', $datos)) {
                $cliente->update(['estado' => $datos['estado']]);
            }

            $refrescado = $cliente->fresh();

            if (! $refrescado instanceof Cliente) {
                throw new \RuntimeException('No se pudo refrescar el cliente.');
            }

            return $refrescado;
        });
    }
}
