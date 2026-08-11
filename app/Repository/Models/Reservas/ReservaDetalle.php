<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\OrigenReservaDetalle;
use App\Repository\Models\Estancias\Estancia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReservaDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'reserva_detalles';

    protected $guarded = ['id'];

    protected $attributes = ['estado' => 1, 'cantidad' => 1, 'origen' => 1];

    protected $casts = [
        'estado' => EstadoReservaDetalle::class,
        'origen' => OrigenReservaDetalle::class,
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'hold_expires_at' => 'datetime',
        'confirmado_at' => 'datetime',
        'cancelado_at' => 'datetime',
        'cantidad' => 'integer',
        'adultos' => 'integer',
        'ninos' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /** @return BelongsTo<Estancia, $this> */
    public function estanciaPadre(): BelongsTo
    {
        return $this->belongsTo(Estancia::class, 'estancia_id');
    }

    /** @return BelongsTo<RecursoReservable, $this> */
    public function reservable(): BelongsTo
    {
        return $this->belongsTo(RecursoReservable::class, 'reservable_id');
    }

    /** @return BelongsTo<self, $this> */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<self, $this> */
    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return HasMany<ReservaHuesped, $this> */
    public function huespedes(): HasMany
    {
        return $this->hasMany(ReservaHuesped::class, 'reserva_detalle_id');
    }

    /** @return HasOne<Estancia, $this> */
    public function estancia(): HasOne
    {
        return $this->hasOne(Estancia::class, 'reserva_detalle_id');
    }

    /** @return HasMany<Estancia, $this> */
    public function estancias(): HasMany
    {
        return $this->hasMany(Estancia::class, 'reserva_detalle_id');
    }
}
