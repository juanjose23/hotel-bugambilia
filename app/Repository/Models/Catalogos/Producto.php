<?php

namespace App\Repository\Models\Catalogos;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Shared\Imagen;
use Carbon\Carbon;
use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $categoria_id
 * @property int|null $marca_id
 * @property int $unidad_medida_id
 * @property int $tipo
 * @property float $rendimiento_porciones
 * @property string|null $tipo_nombre
 * @property string|null $img_base64
 * @property EstadoGeneral $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @use HasFactory<ProductoFactory>
 */
class Producto extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => 'integer',
        'estado' => EstadoGeneral::class,
        'rendimiento_porciones' => 'decimal:2',
    ];

    /** @return BelongsTo<Catalogo, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'categoria_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'marca_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'unidad_medida_id');
    }

    /** @return HasMany<ProductoVariante, $this> */
    public function variantes(): HasMany
    {
        return $this->hasMany(ProductoVariante::class, 'producto_id');
    }

    /** @return MorphOne<Imagen, $this> */
    public function imagen(): MorphOne
    {
        return $this->morphOne(Imagen::class, 'imagenable');
    }

    /** @return HasMany<ProductoKit, $this> */
    public function kitItems(): HasMany
    {
        return $this->hasMany(ProductoKit::class, 'producto_padre_id');
    }
}
