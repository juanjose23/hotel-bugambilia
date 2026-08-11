<?php

declare(strict_types=1);

namespace App\Repository\Models\Estancias;

use App\Enums\Estancias\EstadoEstancia;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $reserva_id
 * @property int|null $habitacion_id
 * @property int|null $usuario_check_in_id
 * @property int|null $usuario_check_out_id
 * @property Carbon|null $check_in_at
 * @property Carbon|null $check_out_at
 * @property Carbon|null $fecha_entrada_programada
 * @property Carbon|null $fecha_salida_programada
 * @property Carbon|null $fecha_check_in_real
 * @property Carbon|null $fecha_check_out_real
 * @property int $cantidad_llaves
 * @property EstadoEstancia $estado
 * @property string|null $observaciones_entrada
 * @property string|null $observaciones_salida
 * @property Cuenta|null $cuenta
 * @property Reserva|null $reserva
 * @property Habitacion|null $habitacion
 */
final class Estancia extends Model
{
    protected $table = 'estancias';

    protected $fillable = [
        'reserva_id', 'reserva_detalle_id', 'habitacion_id', 'usuario_check_in_id', 'usuario_check_out_id',
        'check_in_at', 'check_out_at', 'fecha_entrada_programada', 'fecha_salida_programada',
        'fecha_check_in_real', 'fecha_check_out_real',
        'cantidad_llaves', 'estado', 'observaciones_entrada', 'observaciones_salida',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'fecha_entrada_programada' => 'datetime',
            'fecha_salida_programada' => 'datetime',
            'fecha_check_in_real' => 'datetime',
            'fecha_check_out_real' => 'datetime',
            'cantidad_llaves' => 'integer',
            'estado' => EstadoEstancia::class,
        ];
    }

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /** @return BelongsTo<ReservaDetalle, $this> */
    public function reservaDetalle(): BelongsTo
    {
        return $this->belongsTo(ReservaDetalle::class, 'reserva_detalle_id');
    }

    /** @return BelongsTo<Habitacion, $this> */
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuarioCheckIn(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_check_in_id');
    }

    /** @return BelongsTo<User, $this> */
    public function usuarioCheckOut(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_check_out_id');
    }

    /** @return HasOne<Cuenta, $this> */
    public function cuenta(): HasOne
    {
        return $this->hasOne(Cuenta::class, 'estancia_id');
    }

    /** @return HasMany<Cuenta, $this> */
    public function cuentas(): HasMany
    {
        return $this->hasMany(Cuenta::class, 'estancia_id');
    }
}
