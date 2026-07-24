<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RecursoReservable extends Model
{
    use SoftDeletes;

    protected $table = 'recursos_reservables';

    protected $guarded = ['id'];

    protected $attributes = ['estado' => 1];

    protected $casts = [
        'tipo' => TipoRecursoReservable::class,
        'control_disponibilidad' => ControlDisponibilidad::class,
        'estado' => EstadoRecursoReservable::class,
        'capacidad' => 'integer',
        'duracion_minutos' => 'integer',
    ];

    /** @return HasMany<ReservaDetalle, $this> */
    public function detalles(): HasMany
    {
        return $this->hasMany(ReservaDetalle::class, 'reservable_id');
    }

    /** @return HasOne<Habitacion, $this> */
    public function habitacion(): HasOne
    {
        return $this->hasOne(Habitacion::class, 'reservable_id');
    }

    /** @return HasOne<Espacio, $this> */
    public function espacio(): HasOne
    {
        return $this->hasOne(Espacio::class, 'reservable_id');
    }

    /** @return HasOne<Servicio, $this> */
    public function servicio(): HasOne
    {
        return $this->hasOne(Servicio::class, 'reservable_id');
    }
}
