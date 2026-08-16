<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property EstadoTransaccionPago $estado
 * @property float $monto
 * @property float $monto_base
 * @property float|null $tasa_cambio
 * @property string|null $referencia_pasarela
 * @property Carbon|null $solicitada_at
 * @property Carbon|null $autorizada_at
 * @property Carbon|null $capturada_at
 * @property Carbon|null $fallida_at
 * @property Carbon|null $reembolsada_at
 * @property array<string, mixed>|null $request_payload
 * @property array<string, mixed>|null $response_payload
 * @property array<string, mixed>|null $webhook_payload
 * @property Reserva|null $reserva
 * @property Cuenta|null $cuenta
 */
final class PagoTransaccion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'pago_transacciones';

    protected $guarded = ['id'];

    protected $attributes = [
        'estado' => 1,
        'monto' => 0,
        'monto_base' => 0,
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoTransaccionPago::class,
            'monto' => 'decimal:2',
            'monto_base' => 'decimal:2',
            'tasa_cambio' => 'decimal:6',
            'solicitada_at' => 'datetime',
            'autorizada_at' => 'datetime',
            'capturada_at' => 'datetime',
            'fallida_at' => 'datetime',
            'reembolsada_at' => 'datetime',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'webhook_payload' => 'array',
        ];
    }

    /** @return BelongsTo<PasarelaPago, $this> */
    public function pasarela(): BelongsTo
    {
        return $this->belongsTo(PasarelaPago::class, 'pasarela_pago_id');
    }

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<Venta, $this> */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /** @return BelongsTo<Factura, $this> */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    /** @return BelongsTo<PagoCuenta, $this> */
    public function pagoCuenta(): BelongsTo
    {
        return $this->belongsTo(PagoCuenta::class);
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

    /** @return HasOne<PagoConciliacion, $this> */
    public function conciliacion(): HasOne
    {
        return $this->hasOne(PagoConciliacion::class);
    }
}
