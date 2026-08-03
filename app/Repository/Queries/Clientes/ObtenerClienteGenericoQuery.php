<?php

declare(strict_types=1);

namespace App\Repository\Queries\Clientes;

use App\Repository\Models\Personas\Persona;

final class ObtenerClienteGenericoQuery
{
    public function ejecutar(): ?Persona
    {
        /** @var Persona|null $persona */
        $persona = Persona::query()
            ->where('primer_nombre', 'Público')
            ->where('segundo_nombre', 'General')
            ->first();

        return $persona;
    }
}
