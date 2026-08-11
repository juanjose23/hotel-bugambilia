<?php

declare(strict_types=1);

namespace App\Repository\Models\Limpieza;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class LavanderiaProceso extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'limp_lavanderia_procesos';

    protected $guarded = ['id'];

    protected $casts = [
        'producto_id' => 'integer',
        'producto_variante_id' => 'integer',
        'lote_id' => 'integer',
        'cantidad' => 'decimal:4',
    ];

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * @return BelongsTo<ProductoVariante, $this>
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /**
     * @return BelongsTo<Lote, $this>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}
