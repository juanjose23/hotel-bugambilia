<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\TipoFactura;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Monedas\TasaCambio;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property EstadoFactura $estado
 * @property TipoFactura $tipo
 */
final class Factura extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'factura_serie_id',
        'factura_autorizacion_dgi_id',
        'numero_autorizacion_dgi',
        'rango_autorizado_desde',
        'rango_autorizado_hasta',
        'venta_id',
        'cuenta_id',
        'cliente_id',
        'tipo',
        'estado',
        'numero',
        'numero_correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'moneda_id',
        'moneda_base_id',
        'tasa_cambio_id',
        'tasa_cambio',
        'subtotal',
        'descuento_total',
        'iva_total',
        'servicio_total',
        'propina_total',
        'recargo_total',
        'total',
        'subtotal_base',
        'iva_total_base',
        'total_base',
        'datos_receptor',
        'pdf_path',
        'hash_documento',
        'motivo_anulacion',
        'anulada_at',
        'emitida_por',
        'anulada_por',
    ];

    protected $attributes = [
        'tipo' => 1,
        'estado' => 1,
        'subtotal' => 0,
        'descuento_total' => 0,
        'iva_total' => 0,
        'servicio_total' => 0,
        'propina_total' => 0,
        'recargo_total' => 0,
        'total' => 0,
        'subtotal_base' => 0,
        'iva_total_base' => 0,
        'total_base' => 0,
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoFactura::class,
            'estado' => EstadoFactura::class,
            'numero_correlativo' => 'integer',
            'fecha_emision' => 'datetime',
            'fecha_vencimiento' => 'date',
            'tasa_cambio' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'descuento_total' => 'decimal:2',
            'iva_total' => 'decimal:2',
            'servicio_total' => 'decimal:2',
            'propina_total' => 'decimal:2',
            'recargo_total' => 'decimal:2',
            'total' => 'decimal:2',
            'subtotal_base' => 'decimal:2',
            'iva_total_base' => 'decimal:2',
            'total_base' => 'decimal:2',
            'datos_receptor' => 'array',
            'rango_autorizado_desde' => 'integer',
            'rango_autorizado_hasta' => 'integer',
            'anulada_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<FacturaSerie, $this> */
    public function serie(): BelongsTo
    {
        return $this->belongsTo(FacturaSerie::class, 'factura_serie_id');
    }

    /** @return BelongsTo<FacturaAutorizacionDgi, $this> */
    public function autorizacionDgi(): BelongsTo
    {
        return $this->belongsTo(FacturaAutorizacionDgi::class, 'factura_autorizacion_dgi_id');
    }

    /** @return BelongsTo<Venta, $this> */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return BelongsTo<Moneda, $this> */
    public function monedaBase(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_base_id');
    }

    /** @return BelongsTo<TasaCambio, $this> */
    public function tasaCambioRegistro(): BelongsTo
    {
        return $this->belongsTo(TasaCambio::class, 'tasa_cambio_id');
    }

    /** @return BelongsTo<User, $this> */
    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emitida_por');
    }

    /** @return BelongsTo<User, $this> */
    public function anulador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulada_por');
    }

    /** @return HasMany<FacturaDetalle, $this> */
    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    /** @return HasMany<FacturaFolio, $this> */
    public function folios(): HasMany
    {
        return $this->hasMany(FacturaFolio::class);
    }

    /** @return HasMany<PagoTransaccion, $this> */
    public function transaccionesPasarela(): HasMany
    {
        return $this->hasMany(PagoTransaccion::class);
    }
}
