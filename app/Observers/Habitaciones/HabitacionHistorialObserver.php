<?php

declare(strict_types=1);

namespace App\Observers\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Habitaciones\HabitacionHistorial;
use App\UseCases\Habitaciones\Mutations\GenerarSlugHabitacion;

class HabitacionHistorialObserver
{
    public function creating(Habitacion $habitacion): void
    {
        if (blank($habitacion->slug)) {
            $habitacion->slug = app(GenerarSlugHabitacion::class)->execute($habitacion->nombre);
        }
    }

    public function updating(Habitacion $habitacion): void
    {
        if ($habitacion->isDirty('nombre') && ! $habitacion->isDirty('slug')) {
            $habitacion->slug = app(GenerarSlugHabitacion::class)->execute($habitacion->nombre, $habitacion->id);
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
        $original = $originalRaw !== null ? EstadoHabitacion::tryFrom((int) $originalRaw) : null;

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
