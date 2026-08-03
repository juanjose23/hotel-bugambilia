<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Enums\Restaurante\AreaCocina;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property int|null $categoria_id
 * @property int|null $producto_receta_id
 * @property AreaCocina|null $area_cocina
 * @property string|null $descripcion
 * @property bool $web
 * @property int $estado
 * @property string|null $tiempo_preparacion
 */
final class Plato extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => 'integer',
        'web' => 'boolean',
        'area_cocina' => AreaCocina::class,
    ];

    protected $table = 'platos';

    /** @return BelongsTo<Catalogo, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'categoria_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function receta(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_receta_id');
    }

    /** @return HasManyThrough<ProductoKit, Producto, $this> */
    public function ingredientes(): HasManyThrough
    {
        return $this->hasManyThrough(ProductoKit::class, Producto::class, 'id', 'producto_padre_id', 'producto_receta_id', 'id');
    }

    /** @return MorphMany<Precio, $this> */
    public function precios(): MorphMany
    {
        return $this->morphMany(Precio::class, 'priceable');
    }

    /** @return MorphMany<Imagen, $this> */
    public function imagenes(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'imagenable');
    }

    /** @return MorphToMany<Politica, $this> */
    public function politicas(): MorphToMany
    {
        return $this->morphToMany(Politica::class, 'politicaable')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    /** @return HasMany<PedidoItem, $this> */
    public function itemsPedido(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'plato_id');
    }

    /**
     * @param  Builder<Plato>  $query
     * @return Builder<Plato>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 1);
    }
}
