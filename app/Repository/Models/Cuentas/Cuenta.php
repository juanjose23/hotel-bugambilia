<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Cabecera unificada de cuenta del hotel.
 * Reemplaza CuentaEstancia y CuentaRestaurante.
 *
 * @property int $id
 * @property string $numero_cuenta
 * @property TipoCuenta $tipo_cuenta
 * @property EstadoCuenta $estado
 * @property int|null $cliente_id
 * @property int|null $estancia_id
 * @property int|null $reserva_id
 * @property int|null $moneda_id
 * @property float|null $limite_autorizado
 * @property float $subtotal
 * @property float $descuento_total
 * @property float $impuesto_total
 * @property float $cargo_servicio_total
 * @property float $propina_total
 * @property float $recargo_total
 * @property float $total
 * @property float $total_pagado
 * @property float $saldo
 * @property Carbon $abierta_at
 * @property Carbon|null $cerrada_at
 * @property int|null $abierta_por
 * @property int|null $cerrada_por
 * @property Cliente|null $cliente
 * @property Estancia|null $estancia
 * @property Reserva|null $reserva
 */
final class Cuenta extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected static function booted(): void
    {
        self::creating(function (Cuenta $cuenta): void {
            if ($cuenta->moneda_id === null) {
                $cuenta->moneda_id = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar()?->id;
            }
        });
    }

    protected $fillable = [
        'numero_cuenta', 'tipo_cuenta', 'estado',
        'cliente_id', 'estancia_id', 'reserva_id', 'moneda_id',
        'limite_autorizado',
        'subtotal', 'descuento_total', 'impuesto_total', 'cargo_servicio_total', 'propina_total', 'recargo_total',
        'total', 'total_pagado', 'saldo',
        'abierta_at', 'cerrada_at', 'abierta_por', 'cerrada_por', 'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo_cuenta' => TipoCuenta::class,
            'estado' => EstadoCuenta::class,
            'limite_autorizado' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'descuento_total' => 'decimal:2',
            'impuesto_total' => 'decimal:2',
            'cargo_servicio_total' => 'decimal:2',
            'propina_total' => 'decimal:2',
            'recargo_total' => 'decimal:2',
            'total' => 'decimal:2',
            'total_pagado' => 'decimal:2',
            'saldo' => 'decimal:2',
            'abierta_at' => 'datetime',
            'cerrada_at' => 'datetime',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /** @return BelongsTo<Estancia, $this> */
    public function estancia(): BelongsTo
    {
        return $this->belongsTo(Estancia::class);
    }

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuarioQueAbrio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abierta_por');
    }

    /** @return BelongsTo<User, $this> */
    public function usuarioQueCerro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    /** @return HasMany<CuentaDetalle, $this> */
    public function detalles(): HasMany
    {
        return $this->hasMany(CuentaDetalle::class);
    }

    /** @return HasMany<PagoCuenta, $this> */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCuenta::class);
    }

    /** @return HasMany<CuentaCargo, $this> */
    public function cargos(): HasMany
    {
        return $this->hasMany(CuentaCargo::class);
    }

    /** @return HasMany<Venta, $this> */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    /** @return HasMany<Factura, $this> */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    /** @return HasMany<PagoTransaccion, $this> */
    public function transaccionesPasarela(): HasMany
    {
        return $this->hasMany(PagoTransaccion::class);
    }

    // ─── Métodos de Dominio ───────────────────────────────────────

    public function estaAbierta(): bool
    {
        return $this->estado === EstadoCuenta::ABIERTA;
    }

    public function permiteNuevosCargos(): bool
    {
        return $this->estado->permiteNuevosCargos();
    }

    public function tieneSaldoPendiente(): bool
    {
        return (float) $this->saldo > 0.0;
    }
}
