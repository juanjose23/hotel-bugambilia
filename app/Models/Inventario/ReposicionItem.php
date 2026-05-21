<?php

declare(strict_types=1);

namespace App\Models\Inventario;

use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReposicionItem extends Model
{
    protected $table = 'inv_reposicion_items';

    protected $fillable = [
        'reposicion_id',
        'producto_id',
        'producto_variante_id',
        'cantidad_solicitada',
        'cantidad_surtida',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'float',
        'cantidad_surtida' => 'float',
    ];

    /**
     * @return BelongsTo<Reposicion, $this>
     */
    public function reposicion(): BelongsTo
    {
        return $this->belongsTo(Reposicion::class, 'reposicion_id');
    }

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
}
