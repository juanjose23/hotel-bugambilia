<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Repository\Models\Personas\Persona;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class RegistrarClienteRapido
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Registra un cliente con datos mínimos desde el módulo de restaurante.
     *
     * Crea Persona + PersonaNatural + Cliente con tipo "Regular" por defecto.
     *
     * @param  array{primer_nombre: string, primer_apellido?: string, identificacion?: string, telefono?: string}  $datos
     */
    public function ejecutar(array $datos): Persona
    {
        return DB::transaction(function () use ($datos): Persona {
            $catalogoId = $this->obtenerCatalogoClienteDefault();

            $persona = $this->repositorio->crearPersona([
                'primer_nombre' => $datos['primer_nombre'],
                'segundo_nombre' => $datos['primer_apellido'] ?? '',
                'tipo_persona' => 'natural',
                'telefono' => $datos['telefono'] ?? null,
            ]);

            $naturalData = [
                'persona_id' => $persona->id,
                'primer_nombre' => $datos['primer_nombre'],
                'primer_apellido' => $datos['primer_apellido'] ?? '',
            ];

            if (! empty($datos['identificacion'])) {
                $naturalData['tipo_identificacion'] = 'CEDULA';
                $naturalData['numero_identificacion'] = $datos['identificacion'];
            }

            $this->repositorio->crearPersonaNatural($naturalData);

            $this->repositorio->crearCliente([
                'persona_id' => $persona->id,
                'catalogo_id' => $catalogoId,
                'estado' => 1,
            ]);

            return $persona->fresh() ?? $persona;
        });
    }

    private function obtenerCatalogoClienteDefault(): int
    {
        $catalogo = $this->repositorio->obtenerCatalogoClienteRegular();

        return $catalogo !== null ? $catalogo->id : 1;
    }
}
