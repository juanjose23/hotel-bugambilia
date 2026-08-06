<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SustitucionIngrediente extends Model
{
    use SoftDeletes;

    protected $table = 'restaurante_sustituciones_ingredientes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cantidad_requerida' => 'decimal:4',
            'cantidad_usada' => 'decimal:4',
            'estado' => 'integer',
        ];
    }

    /** @return BelongsTo<PedidoItem, $this> */
    public function pedidoItem(): BelongsTo
    {
        return $this->belongsTo(PedidoItem::class);
    }

    /** @return BelongsTo<Plato, $this> */
    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class);
    }

    /** @return BelongsTo<Producto, $this> */
    public function productoOriginal(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_original_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function varianteOriginal(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_original_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function productoSustituto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_sustituto_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function varianteSustituta(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_sustituta_id');
    }

    /** @return BelongsTo<User, $this> */
    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }
}
