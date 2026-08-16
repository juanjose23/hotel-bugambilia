<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Usuarios\ConflictoIdentidad;

final class ConflictoIdentidadPersistencia
{
    /**
     * @param  array<string, mixed>  $datosProvidos
     * @param  array<string, mixed>  $datosExistentes
     */
    public function crearPendiente(
        Persona $persona,
        TipoConflictoIdentidad $tipoConflicto,
        array $datosProvidos,
        array $datosExistentes,
        ?int $usuarioId,
    ): ConflictoIdentidad {
        return ConflictoIdentidad::create([
            'persona_id' => $persona->id,
            'tipo_conflicto' => $tipoConflicto,
            'datos_providos' => $datosProvidos,
            'datos_existentes' => $datosExistentes,
            'estado' => EstadoConflictoIdentidad::Pendiente,
            'creado_por' => $usuarioId,
        ]);
    }
}
