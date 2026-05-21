<?php

declare(strict_types=1);

namespace App\Models\Inventario;

use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ParStock extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'inv_par_stock';

    protected $fillable = [
        'producto_id',
        'producto_variante_id',
        'ubicacion_id',
        'stock_minimo',
        'stock_objetivo',
    ];

    protected $casts = [
        'stock_minimo' => 'float',
        'stock_objetivo' => 'float',
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
     * @return BelongsTo<Ubicacion, $this>
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }
}
