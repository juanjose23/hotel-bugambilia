<?php

declare(strict_types=1);

namespace App\Repository\Models\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $codigo_reserva
 * @property string|null $nombre_cliente
 * @property EstadoReserva $estado
 * @property int|null $habitacion_id
 * @property int|null $espacio_id
 * @property bool $solicita_cuenta
 * @property float|null $limite_cuenta_solicitado
 * @property Carbon|null $fecha_check_in
 * @property Carbon|null $fecha_check_out
 */
final class Reserva extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'reservas';

    protected $attributes = [
        'solicita_cuenta' => false,
    ];

    protected $fillable = [
        'codigo_reserva',
        'cliente_id',
        'nombre_cliente',
        'telefono_cliente',
        'email_cliente',
        'tipo_reserva',
        'habitacion_id',
        'espacio_id',
        'servicio_id',
        'promocion_id',
        'fecha_check_in',
        'fecha_check_out',
        'hora_reserva',
        'adultos',
        'ninos',
        'solicita_cuenta',
        'limite_cuenta_solicitado',
        'estado',
        'subtotal',
        'descuento',
        'total',
        'moneda_id',
        'tipo_pago',
        'total_pagado',
        'saldo',
        'notas',
        'acompanantes',
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
        'tipo_pago' => TipoPagoReserva::class,
        'total_pagado' => 'decimal:2',
        'saldo' => 'decimal:2',
        'acompanantes' => 'array',
    ];

    /**
     * @return Attribute<int, never>
     */
    protected function noches(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if (! ($this->fecha_check_in instanceof Carbon) || ! ($this->fecha_check_out instanceof Carbon)) {
                    return 1;
                }

                $checkIn = $this->fecha_check_in->copy()->startOfDay();
                $checkOut = $this->fecha_check_out->copy()->startOfDay();

                return max(1, (int) $checkIn->diffInDays($checkOut));
            },
        );
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
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

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
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

    /** @return HasMany<Estancia, $this> */
    public function estancias(): HasMany
    {
        return $this->hasMany(Estancia::class);
    }

    /** @return HasMany<Cuenta, $this> */
    public function cuentas(): HasMany
    {
        return $this->hasMany(Cuenta::class);
    }

    /** @return HasMany<ReservaBitacora, $this> */
    public function bitacora(): HasMany
    {
        return $this->hasMany(ReservaBitacora::class);
    }

    /**
     * Obtiene la entrada más reciente de un tipo específico de bitácora.
     *
     * @return array<string, mixed>|null
     */
    public function ultimaEntradaBitacora(string $tipo): ?array
    {
        /** @var ReservaBitacora|null $entrada */
        $entrada = $this->bitacora()
            ->where('tipo', $tipo)
            ->latest()
            ->first();

        if ($entrada === null) {
            return null;
        }

        /** @var array<string, mixed>|null $datos */
        $datos = $entrada->datos;

        return $datos;
    }

    /**
     * Crea una entrada en la bitácora.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crearEntradaBitacora(string $tipo, array $datos): ReservaBitacora
    {
        return $this->bitacora()->create([
            'tipo' => $tipo,
            'datos' => $datos,
        ]);
    }

    /**
     * Actualiza la entrada más reciente de un tipo, o crea una nueva si no existe.
     *
     * @param  array<string, mixed>  $datos
     */
    public function actualizarOCrearEntradaBitacora(string $tipo, array $datos): ReservaBitacora
    {
        $entrada = $this->bitacora()->where('tipo', $tipo)->latest()->first();

        if ($entrada !== null) {
            $entrada->update(['datos' => $datos]);

            return $entrada;
        }

        return $this->crearEntradaBitacora($tipo, $datos);
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
