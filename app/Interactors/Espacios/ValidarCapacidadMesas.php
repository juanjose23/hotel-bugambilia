<?php

declare(strict_types=1);

namespace App\Interactors\Espacios;

use App\BusinessLogic\Restaurante\Mesas\ValidarCapacidadMesasRestaurante;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Queries\Espacios\ConsultarCapacidadMesas;
use DomainException;
use InvalidArgumentException;
use OverflowException;

/**
 * Caso de Uso: Validar que un restaurante puede recibir más mesas
 * antes de crear un nuevo sub-espacio de tipo MESA.
 *
 * Lanza:
 *   - InvalidArgumentException  → si el padre no es RESTAURANTE o la mesa no es MESA
 *   - OverflowException          → si se superó el límite configurado en meta_datos.capacidad_mesas
 *
 * Retorna el Espacio MESA creado si la validación pasa y $crearSiValida = true.
 */
class ValidarCapacidadMesas
{
    public function __construct(
        private readonly ConsultarCapacidadMesas $consultarCapacidad,
        private readonly ValidarCapacidadMesasRestaurante $validarCapacidadDomain,
    ) {}

    /**
     * Valida la capacidad y, opcionalmente, crea la mesa si hay espacio disponible.
     *
     * @param  int  $restauranteId  ID del espacio padre tipo RESTAURANTE
     * @param  bool  $crearSiValida  Si true, crea el sub-espacio MESA con $datosMesa
     * @param  array<string, mixed>  $datosMesa  Datos para Espacio::create() (se ignoran si $crearSiValida=false)
     *
     * @throws InvalidArgumentException Si el padre no es RESTAURANTE o el tipo de la nueva mesa no es MESA
     * @throws OverflowException Si la capacidad configurada ha sido alcanzada
     */
    public function execute(
        int $restauranteId,
        bool $crearSiValida = false,
        array $datosMesa = [],
    ): ?Espacio {
        // Validar también usando la regla de negocio de dominio
        $padre = Espacio::find($restauranteId);
        if ($padre instanceof Espacio) {
            $meta = is_array($padre->meta_datos) ? $padre->meta_datos : [];
            try {
                $this->validarCapacidadDomain->validar($restauranteId, $meta);
            } catch (DomainException $e) {
                throw new OverflowException($e->getMessage(), (int) $e->getCode(), $e);
            }
        }

        // 1. Obtener resumen de capacidad (lanza InvalidArgumentException si no es RESTAURANTE)
        $capacidad = $this->consultarCapacidad->execute($restauranteId);

        // 2. Verificar que hay lugar disponible
        if (! $capacidad['puede_agregar']) {
            $limite = $capacidad['capacidad_configurada'];
            $actuales = $capacidad['mesas_activas'];

            throw new OverflowException(
                "El restaurante \"{$capacidad['restaurante_nombre']}\" ha alcanzado su capacidad máxima de {$limite} mesas. "
                ."Actualmente tiene {$actuales} mesas registradas. "
                .'Aumente la capacidad configurada en meta_datos.capacidad_mesas antes de agregar más.'
            );
        }

        // 3. Si no se pidió crear, solo retorna null (modo validación pura)
        if (! $crearSiValida) {
            return null;
        }

        // 4. Garantizar que los datos de la nueva mesa sean coherentes
        if (isset($datosMesa['tipo']) && $datosMesa['tipo'] !== TipoEspacio::MESA->value && $datosMesa['tipo'] !== TipoEspacio::MESA) {
            throw new InvalidArgumentException(
                'El tipo del sub-espacio que se intenta crear debe ser MESA.'
            );
        }

        // Forzar padre y tipo correcto
        $datosMesa['padre_id'] = $restauranteId;
        $datosMesa['tipo'] = TipoEspacio::MESA->value;

        return Espacio::create($datosMesa);
    }
}
