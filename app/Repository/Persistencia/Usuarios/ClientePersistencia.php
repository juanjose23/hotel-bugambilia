<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Catalogos\ObtenerCatalogoClienteRegularQuery;

final class ClientePersistencia
{
    public function __construct(
        private readonly ObtenerCatalogoClienteRegularQuery $obtenerCatalogoClienteRegularQuery = new ObtenerCatalogoClienteRegularQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearDesdePersona(Persona $persona, array $datos): Cliente
    {
        $catalogoId = $datos['catalogo_id'] ?? $this->obtenerCatalogoClienteRegularQuery->obtener()?->id;

        return Cliente::create([
            'persona_id' => $persona->id,
            'catalogo_id' => $catalogoId,
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
