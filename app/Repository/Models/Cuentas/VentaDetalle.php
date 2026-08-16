<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Repository\Models\Facturacion\FacturaDetalle;
use App\Repository\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Renglón de una venta. Fotografía histórica inmutable.
 *
 * @property int $id
 * @property int $venta_id
 * @property string $concepto
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property float $descuento
 * @property float $impuesto
 * @property float $servicio
 * @property float $propina
 * @property float $recargo
 * @property float $total_linea
 * @property string|null $origen_type
 * @property int|null $origen_id
 * @property int $moneda_id
 * @property Moneda|null $moneda
 */
final class VentaDetalle extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'venta_detalles';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'servicio' => 'decimal:2',
            'propina' => 'decimal:2',
            'recargo' => 'decimal:2',
            'total_linea' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Venta, $this> */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return HasMany<FacturaDetalle, $this> */
    public function facturaDetalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function origen(): MorphTo
    {
        return $this->morphTo();
    }
}
