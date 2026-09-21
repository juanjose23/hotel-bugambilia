<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Clientes;

use App\Interactors\Usuarios\Identidad\ActualizarDatosPersona;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Persistencia\Usuarios\ClientePersistencia;
use Illuminate\Support\Facades\DB;

final readonly class ActualizarCliente
{
    public function __construct(
        private ActualizarDatosPersona $actualizarPersona,
        private ClientePersistencia $clientePersistencia,
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

            return $this->clientePersistencia->actualizar($cliente, $datos);
        });
    }
}
