<?php

declare(strict_types=1);

namespace App\Repository\Observers\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Interactors\Habitaciones\GenerarSlugHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Habitaciones\HabitacionHistorial;

class HabitacionHistorialObserver
{
    public function __construct(
        private readonly GenerarSlugHabitacion $generarSlug,
    ) {}

    public function creating(Habitacion $habitacion): void
    {
        if (blank($habitacion->slug)) {
            $habitacion->slug = $this->generarSlug->ejecutar($habitacion->nombre ?? '');
        }
    }

    public function updating(Habitacion $habitacion): void
    {
        if ($habitacion->isDirty('nombre') && ! $habitacion->isDirty('slug')) {
            $habitacion->slug = $this->generarSlug->ejecutar($habitacion->nombre ?? '', $habitacion->id);
        }
    }

    public function created(Habitacion $habitacion): void
    {
        $this->registrarTransicion($habitacion);
    }

    public function updated(Habitacion $habitacion): void
    {
        if (! $habitacion->wasChanged('estado')) {
            return;
        }

        $this->registrarTransicion($habitacion);
    }

    private function registrarTransicion(Habitacion $habitacion): void
    {
        $originalRaw = $habitacion->getOriginal('estado');
        $original = null;
        if ($originalRaw instanceof EstadoEspacio) {
            $original = $originalRaw;
        } elseif ($originalRaw !== null) {
            $original = is_numeric($originalRaw) ? EstadoEspacio::tryFrom((int) $originalRaw) : null;
        }

        HabitacionHistorial::create([
            'model_type' => Habitacion::class,
            'model_id' => $habitacion->id,
            'estado_anterior' => $original?->label(),
            'estado_nuevo' => $habitacion->estado->label(),
            'usuario_id' => auth()->id(),
            'comentario' => null,
        ]);
    }
}
