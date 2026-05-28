<?php

declare(strict_types=1);

namespace App\Models\Espacios;

use App\Enums\HabitacionesEspacios\EstadoServicioHabitacion;
use App\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicioEspacio extends Model
{
    use SoftDeletes;

    protected $table = 'servicios_espacio';

    protected $guarded = ['id'];

    protected $casts = [
        'incluido' => 'boolean',
        'estado' => EstadoServicioHabitacion::class,
    ];

    /**
     * Relación con el Servicio
     *
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Relación con el Espacio Físico
     *
     * @return BelongsTo<Espacio, $this>
     */
    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'espacio_id');
    }
}
