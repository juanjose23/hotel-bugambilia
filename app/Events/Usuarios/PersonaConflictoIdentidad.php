<?php

declare(strict_types=1);

namespace App\Events\Usuarios;

use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PersonaConflictoIdentidad
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ConflictoIdentidad $conflicto,
        public Persona $personaExistente,
        public TipoConflictoIdentidad $tipoConflicto,
        /** @var array<string, mixed> */
        public array $datosProvidos,
        /** @var array<string, mixed> */
        public array $datosExistentes,
    ) {}
}
