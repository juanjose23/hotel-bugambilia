<?php

declare(strict_types=1);

namespace App\Repository\Models\Facturacion;

use App\Repository\Models\Cuentas\VentaDetalle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class FacturaDetalle extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'factura_detalles';

    protected $guarded = ['id'];

    protected $attributes = [
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
        'descuento' => 0,
        'iva_porcentaje' => 15,
        'iva' => 0,
        'total_linea' => 0,
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'iva_porcentaje' => 'decimal:4',
            'iva' => 'decimal:2',
            'total_linea' => 'decimal:2',
            'meta_datos' => 'array',
        ];
    }

    /** @return BelongsTo<Factura, $this> */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    /** @return BelongsTo<VentaDetalle, $this> */
    public function ventaDetalle(): BelongsTo
    {
        return $this->belongsTo(VentaDetalle::class);
    }
}
