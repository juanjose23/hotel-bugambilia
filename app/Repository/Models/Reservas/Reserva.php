<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $codigo_reserva
 * @property string|null $nombre_cliente
 * @property EstadoReserva $estado
 * @property int|null $habitacion_id
 * @property int|null $espacio_id
 */
class Reserva extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'reservas';

    protected $fillable = [
        'codigo_reserva', 'cliente_id', 'nombre_cliente', 'telefono_cliente',
        'email_cliente', 'tipo_reserva', 'habitacion_id', 'espacio_id',
        'servicio_id', 'promocion_id', 'fecha_check_in', 'fecha_check_out', 'hora_reserva',
        'adultos', 'ninos', 'solicita_cuenta', 'limite_cuenta_solicitado', 'estado',
        'subtotal', 'descuento', 'total', 'notas', 'acompanantes',
    ];

    protected $casts = [
        'tipo_reserva' => TipoReserva::class,
        'estado' => EstadoReserva::class,
        'fecha_check_in' => 'date',
        'fecha_check_out' => 'date',
        'adultos' => 'integer',
        'ninos' => 'integer',
        'solicita_cuenta' => 'boolean',
        'limite_cuenta_solicitado' => 'decimal:2',
        'promocion_id' => 'integer',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
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

    /** @return BelongsTo<Promocion, $this> */
    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
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

    /** @return HasMany<ReservaDetalle, $this> */
    public function detalles(): HasMany
    {
        return $this->hasMany(ReservaDetalle::class);
    }

    /** @return HasMany<ReservaEstadoHistorial, $this> */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(ReservaEstadoHistorial::class);
    }

    /** @return HasOne<Estancia, $this> */
    public function estancia(): HasOne
    {
        return $this->hasOne(Estancia::class);
    }

    /** @return HasMany<Cuenta, $this> */
    public function cuentas(): HasMany
    {
        return $this->hasMany(Cuenta::class);
    }

    /** @return HasManyThrough<ReservaHuesped, ReservaDetalle, $this> */
    public function huespedes(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReservaHuesped::class,
            ReservaDetalle::class,
            'reserva_id',
            'reserva_detalle_id',
        );
    }
}
