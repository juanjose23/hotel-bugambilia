<?php

declare(strict_types=1);

namespace App\Repository\Models\Usuarios;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int|null $persona_id
 * @property TipoConflictoIdentidad $tipo_conflicto
 * @property array<string, mixed> $datos_providos
 * @property array<string, mixed> $datos_existentes
 * @property EstadoConflictoIdentidad $estado
 * @property int|null $creado_por
 * @property int|null $resuelto_por
 * @property Carbon|null $resuelto_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static self create(array<string, mixed> $attributes)
 */
final class ConflictoIdentidad extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'conflictos_identidad';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo_conflicto' => TipoConflictoIdentidad::class,
        'estado' => EstadoConflictoIdentidad::class,
        'datos_providos' => 'array',
        'datos_existentes' => 'array',
        'resuelto_en' => 'datetime',
    ];

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /** @return BelongsTo<User, $this> */
    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }
}
