<?php

declare(strict_types=1);

namespace App\Observers\Espacios;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\HabitacionHistorial;

class EspacioHistorialObserver
{
    public function created(Espacio $espacio): void
    {
        $this->registrarTransicion($espacio);
    }

    public function updated(Espacio $espacio): void
    {
        if (! $espacio->wasChanged('estado')) {
            return;
        }

        $this->registrarTransicion($espacio);
    }

    private function registrarTransicion(Espacio $espacio): void
    {
        $originalRaw = $espacio->getOriginal('estado');
        $original = null;
        if ($originalRaw instanceof EstadoEspacio) {
            $original = $originalRaw;
        } elseif ($originalRaw !== null) {
            $original = EstadoEspacio::tryFrom((int) $originalRaw);
        }

        HabitacionHistorial::create([
            'model_type' => Espacio::class,
            'model_id' => $espacio->id,
            'estado_anterior' => $original?->getLabel(),
            'estado_nuevo' => $espacio->estado->getLabel(),
            'usuario_id' => auth()->id(),
            'comentario' => null,
        ]);
    }
}
