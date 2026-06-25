<?php

declare(strict_types=1);

namespace App\Models\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $limpiable_type
 * @property int $limpiable_id
 * @property int|null $personal_id
 * @property int|null $creador_id
 * @property string $prioridad
 * @property EstadoLimpieza $estado
 * @property string|null $notas
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|Habitacion|Espacio|Ubicacion|null $limpiable
 * @property-read User|null $personal
 * @property-read User|null $creador
 */
class SolicitudLimpieza extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'solicitud_limpiezas';

    protected $guarded = ['id'];

    protected $casts = [
        'limpiable_id' => 'integer',
        'personal_id' => 'integer',
        'creador_id' => 'integer',
        'estado' => EstadoLimpieza::class,
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function limpiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function personal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'personal_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * @return HasMany<LimpiezaEjecucion, $this>
     */
    public function ejecuciones(): HasMany
    {
        return $this->hasMany(LimpiezaEjecucion::class, 'solicitud_id');
    }
}
