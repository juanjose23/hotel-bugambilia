<?php

declare(strict_types=1);

namespace App\Repository\Queries\Espacios;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use InvalidArgumentException;

/**
 * Caso de Uso: Consultar la capacidad total de mesas configurada en un restaurante
 * y el número de sub-espacios tipo MESA activos que tiene registrados.
 *
 * Devuelve un resumen con:
 *   - capacidad_configurada : límite definido en meta_datos.capacidad_mesas (null = sin límite)
 *   - mesas_activas         : mesas (tipo=MESA) hijas sin soft-delete
 *   - mesas_disponibles     : cuántas se pueden agregar aún (null = sin límite)
 *   - puede_agregar         : bool de conveniencia
 */
class ConsultarCapacidadMesas
{
    /**
     * @return array{
     *   restaurante_id: int,
     *   restaurante_nombre: string,
     *   capacidad_configurada: int|null,
     *   mesas_activas: int,
     *   mesas_disponibles: int|null,
     *   puede_agregar: bool,
     * }
     *
     * @throws InvalidArgumentException Si el espacio no es de tipo RESTAURANTE
     */
    public function execute(int $restauranteId): array
    {
        $restaurante = Espacio::findOrFail($restauranteId);

        if ($restaurante->tipo !== TipoEspacio::RESTAURANTE) {
            throw new InvalidArgumentException(
                "El espacio [{$restauranteId}] no es de tipo RESTAURANTE; es: {$restaurante->tipo->value}."
            );
        }

        $capacidadConfigurada = $this->resolveCapacidadConfigurada($restaurante);

        $mesasActivas = $restaurante->hijos()
            ->where('tipo', TipoEspacio::MESA->value)
            ->count();

        $mesasDisponibles = $capacidadConfigurada !== null
            ? max(0, $capacidadConfigurada - $mesasActivas)
            : null;

        $puedeAgregar = $capacidadConfigurada === null || $mesasActivas < $capacidadConfigurada;

        return [
            'restaurante_id' => $restaurante->id,
            'restaurante_nombre' => $restaurante->nombre,
            'capacidad_configurada' => $capacidadConfigurada,
            'mesas_activas' => $mesasActivas,
            'mesas_disponibles' => $mesasDisponibles,
            'puede_agregar' => $puedeAgregar,
        ];
    }

    /**
     * Extrae la capacidad máxima de mesas desde meta_datos.capacidad_mesas.
     * Acepta int, string numérico o null. Devuelve null si no está configurada.
     */
    private function resolveCapacidadConfigurada(Espacio $restaurante): ?int
    {
        $metaDatos = $restaurante->meta_datos;

        if (! is_array($metaDatos) || ! isset($metaDatos['capacidad_mesas'])) {
            return null;
        }

        $valor = $metaDatos['capacidad_mesas'];

        if (! is_numeric($valor) || (int) $valor < 1) {
            return null;
        }

        return (int) $valor;
    }
}
