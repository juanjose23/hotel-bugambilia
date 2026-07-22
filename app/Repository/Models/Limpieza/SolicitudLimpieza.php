<?php

declare(strict_types=1);

namespace App\Repository\Models\Limpieza;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\User;
use Database\Factories\Limpieza\SolicitudLimpiezaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/** @use HasFactory<SolicitudLimpiezaFactory> */
class SolicitudLimpieza extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'solicitud_limpiezas';

    protected $guarded = ['id'];

    protected $casts = [
        'limpiable_id' => 'integer',
        'personal_id' => 'integer',
        'creador_id' => 'integer',
        'estado' => EstadoLimpieza::class,
    ];

    /** @return MorphTo<Model, $this> */
    public function limpiable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function personal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'personal_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /** @return HasMany<LimpiezaEjecucion, $this> */
    public function ejecuciones(): HasMany
    {
        return $this->hasMany(LimpiezaEjecucion::class, 'solicitud_id');
    }
}
