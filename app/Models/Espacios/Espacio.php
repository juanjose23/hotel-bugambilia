<?php

declare(strict_types=1);

namespace App\Models\Espacios;

use App\Enums\Espacios\EstadoEspacio;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property string|null $nombre
 */
class Espacio extends Model implements AuditableContract
{
    /** @use HasFactory<Factory<static>> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'espacios';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoEspacio::class,
        'capacidad' => 'integer',
    ];

    /**
     * @return BelongsTo<Catalogo, $this>
     */
    public function tipoEspacio(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'tipo_espacio_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /**
     * @return MorphMany<ActivoAsignacion, $this>
     */
    public function asignacionesActivos(): MorphMany
    {
        return $this->morphMany(ActivoAsignacion::class, 'asignable');
    }
}
