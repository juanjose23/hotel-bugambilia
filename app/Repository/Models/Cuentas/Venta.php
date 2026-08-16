<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Enums\Cuentas\EstadoVenta;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Documento de venta definitivo generado al cerrar una cuenta.
 * Datos congelados: no se modifican una vez emitida.
 *
 * @property int $id
 * @property string $numero_venta
 * @property int|null $cuenta_id
 * @property int|null $cliente_id
 * @property int|null $moneda_id
 * @property float $subtotal
 * @property float $descuento_total
 * @property float $impuesto_total
 * @property float $servicio_total
 * @property float $propina_total
 * @property float $recargo_total
 * @property float $total
 * @property EstadoVenta $estado
 * @property array<string, mixed>|null $datos_fiscales
 * @property int|null $creada_por
 * @property int|null $anulada_por
 * @property Carbon|null $anulada_en
 */
final class Venta extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'ventas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento_total' => 'decimal:2',
            'impuesto_total' => 'decimal:2',
            'servicio_total' => 'decimal:2',
            'propina_total' => 'decimal:2',
            'recargo_total' => 'decimal:2',
            'total' => 'decimal:2',
            'estado' => EstadoVenta::class,
            'datos_fiscales' => 'array',
            'anulada_en' => 'datetime',
        ];
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    /** @return HasMany<VentaDetalle, $this> */
    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
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
}
