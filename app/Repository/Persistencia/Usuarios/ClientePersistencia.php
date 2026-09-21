<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Catalogos\ObtenerCatalogoClienteRegularQuery;

final readonly class ClientePersistencia
{
    public function __construct(
        private ObtenerCatalogoClienteRegularQuery $obtenerCatalogoClienteRegularQuery = new ObtenerCatalogoClienteRegularQuery,
    ) {}

    public function buscarPorIdConTipo(int $id): ?Cliente
    {
        return Cliente::query()
            ->with('tipoCliente')
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearDesdePersona(Persona $persona, array $datos): Cliente
    {
        $catalogoId = $datos['catalogo_id']
            ?? $this->obtenerCatalogoClienteRegularQuery->obtener()->id
            ?? Catalogo::query()->first()?->id;

        if ($catalogoId === null) {
            $tipo = CatalogoTipo::query()->firstOrCreate(
                ['codigo' => 'TIPO-CLIENTE'],
                ['nombre' => 'Tipo de Cliente', 'estado' => EstadoGeneral::Activo]
            );
            $catalogo = Catalogo::query()->firstOrCreate(
                ['codigo' => 'CLI_REGULAR'],
                [
                    'catalogo_tipo_id' => $tipo->id,
                    'nombre' => 'Cliente Regular',
                    'estado' => EstadoGeneral::Activo,
                ]
            );
            $catalogoId = $catalogo->id;
        }

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

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Cliente $cliente, array $datos): Cliente
    {
        $cambios = [];
        if (array_key_exists('catalogo_id', $datos)) {
            $cambios['catalogo_id'] = $datos['catalogo_id'];
        }
        if (array_key_exists('estado', $datos)) {
            $cambios['estado'] = $datos['estado'];
        }

        if ($cambios !== []) {
            $cliente->update($cambios);
        }

        return $cliente->fresh() ?? $cliente;
    }
}
