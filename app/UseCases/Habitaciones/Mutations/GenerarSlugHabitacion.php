<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Mutations;

use App\Models\Habitaciones\Habitacion;
use Illuminate\Support\Str;

class GenerarSlugHabitacion
{
    /**
     * Genera un slug único para la habitación basado en su nombre.
     */
    public function execute(string $nombre, ?int $ignoreId = null): string
    {
        $slug = Str::slug($nombre);

        if (blank($slug)) {
            $slug = 'habitacion';
        }

        $baseSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Habitacion::withTrashed()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
