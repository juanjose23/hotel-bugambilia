<?php

namespace App\Models\Catalogos;

use App\Models\General\Imagen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int $tipo
 * @property string|null $tipo_nombre
 * @property string|null $img_base64
 */
class Producto extends Model implements AuditableContract
{
    //
    use Auditable, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'marca_id',
        'nombre',
        'descripcion',
        'unidad_medida_id',
        'tipo',
        'estado',
    ];

    protected $casts = [
        'tipo' => 'integer',
        'estado' => 'integer',
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
}
