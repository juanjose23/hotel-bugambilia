<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Registro de pago/abono realizado contra una Cuenta.
 * Reemplaza PagoRestaurante y la lógica de pagos en MovimientoCuentaEstancia.
 *
 * @property int $id
 * @property int $cuenta_id
 * @property MetodoPago $forma_pago
 * @property int|null $moneda_id
 * @property EstadoPago $estado
 * @property float $monto
 * @property float $propina
 * @property string|null $referencia_transaccion
 * @property string|null $observaciones
 * @property int|null $usuario_id
 * @property Carbon $created_at
 * @property Cuenta $cuenta
 */
final class PagoCuenta extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'pagos_cuenta';

    protected $fillable = [
        'cuenta_id', 'venta_id', 'forma_pago', 'moneda_id', 'estado',
        'monto', 'propina',
        'referencia_transaccion', 'observaciones',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'forma_pago' => MetodoPago::class,
            'estado' => EstadoPago::class,
            'monto' => 'decimal:2',
            'propina' => 'decimal:2',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Venta, $this> */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /** @return HasMany<PagoTransaccion, $this> */
    public function transaccionesPasarela(): HasMany
    {
        return $this->hasMany(PagoTransaccion::class);
    }

    // ─── Métodos de Dominio ───────────────────────────────────────

    /** Monto total incluyendo propina */
    public function montoTotal(): float
    {
        return round((float) $this->monto + (float) $this->propina, 2);
    }
}
