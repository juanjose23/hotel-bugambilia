<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Reserva extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'reservas';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo_reserva' => TipoReserva::class,
        'estado' => EstadoReserva::class,
        'fecha_check_in' => 'date',
        'fecha_check_out' => 'date',
        'adultos' => 'integer',
        'ninos' => 'integer',
        'total' => 'decimal:2',
        'acompanantes' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * @return BelongsTo<Habitacion, $this>
     */
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    /**
     * @return BelongsTo<Espacio, $this>
     */
    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'espacio_id');
    }

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * @return HasMany<ReservaServicio, $this>
     */
    public function serviciosAdicionalesItems(): HasMany
    {
        return $this->hasMany(ReservaServicio::class, 'reserva_id');
    }

    /**
     * @return BelongsToMany<Servicio, $this>
     */
    public function serviciosAdicionales(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'reserva_servicios', 'reserva_id', 'servicio_id')
            ->withPivot(['cantidad', 'precio'])
            ->withTimestamps();
    }
}
