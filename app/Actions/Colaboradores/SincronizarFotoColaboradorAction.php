<?php

declare(strict_types=1);

namespace App\Actions\Colaboradores;

use App\Repository\Models\Colaboradores\Colaborador;
use Illuminate\Support\Facades\Storage;

final class SincronizarFotoColaboradorAction
{
    public function ejecutar(Colaborador $colaborador, ?string $nuevaFotoUrl): void
    {
        if ($nuevaFotoUrl === null || trim($nuevaFotoUrl) === '') {
            return;
        }

        $imagenActual = $colaborador->imagen;

        if ($imagenActual && $imagenActual->url && $imagenActual->url !== $nuevaFotoUrl) {
            Storage::disk('public')->delete($imagenActual->url);
        }

        $colaborador->imagen()->updateOrCreate(
            [],
            ['url' => $nuevaFotoUrl]
        );
    }
}
