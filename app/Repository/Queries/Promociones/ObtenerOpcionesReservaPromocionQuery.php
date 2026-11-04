<?php

declare(strict_types=1);

namespace App\Repository\Queries\Promociones;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Model;

final readonly class ObtenerOpcionesReservaPromocionQuery
{
    /** @return list<array{key: string, tipo: string, recurso_id: int, nombre: string, necesita_salida: bool}> */
    public function ejecutar(Promocion $promocion): array
    {
        $promocion->loadMissing('items.item');
        $incluidos = $promocion->items->filter(fn ($i): bool => (bool) ($i->getAttribute('incluido') ?? true));
        if ($incluidos->isNotEmpty()) {
            $principal = $incluidos->first(fn ($i): bool => $i->item instanceof Habitacion)
                ?? $incluidos->first(fn ($i): bool => $i->item instanceof Espacio)
                ?? $incluidos->first(fn ($i): bool => $i->item instanceof Servicio);
            if ($principal !== null && $principal->item instanceof Model) {
                return [$this->opcion($principal->item)];
            }
            if ($incluidos->every(fn ($i): bool => $i->item instanceof Plato)) {
                return [['key' => 'restaurante:0', 'tipo' => 'restaurante', 'recurso_id' => 0,
                    'nombre' => 'Mesa y menú de la promoción', 'necesita_salida' => false]];
            }

            return [];
        }
        $opciones = [];
        foreach (Habitacion::query()->where('web', true)->where('estado', 1)->get() as $habitacion) {
            $opciones[] = $this->opcion($habitacion);
        }
        foreach (Servicio::query()->where('web', true)->where('estado', 1)->get() as $servicio) {
            $opciones[] = $this->opcion($servicio);
        }
        if (Espacio::query()->where('tipo', TipoEspacio::RESTAURANTE)->where('web', true)->exists()) {
            $opciones[] = ['key' => 'restaurante:0', 'tipo' => 'restaurante', 'recurso_id' => 0,
                'nombre' => 'Reservar mesa en el restaurante', 'necesita_salida' => false];
        }

        return $opciones;
    }

    /** @return array{key: string, tipo: string, recurso_id: int, nombre: string, necesita_salida: bool} */
    private function opcion(Model $modelo): array
    {
        $tipo = $modelo instanceof Habitacion ? 'habitacion' : ($modelo instanceof Servicio ? 'servicio' : 'restaurante');
        $nombre = $modelo->getAttribute('nombre') ?? $modelo->getAttribute('numero') ?? 'Recurso de la promoción';

        $id = $modelo->getKey();
        if (! is_numeric($id) || ! is_string($nombre)) {
            throw new \DomainException('La promoción contiene un recurso incompleto.');
        }

        return ['key' => $tipo.':'.$id, 'tipo' => $tipo, 'recurso_id' => (int) $id,
            'nombre' => (string) $nombre, 'necesita_salida' => $modelo instanceof Habitacion];
    }
}
