<?php

namespace App\Repository\Models\Catalogos;

use App\Enums\Shared\EstadoGeneral;
use Carbon\Carbon;
use Database\Factories\CatalogoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string|null $prefijo
 * @property int|null $padre_id
 * @property int $orden
 * @property string|null $descripcion
 * @property EstadoGeneral $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @use HasFactory<CatalogoFactory>
 */
class Catalogo extends Model implements AuditableContract
{
    /** @phpstan-ignore missingType.generics */
    use Auditable, HasFactory;

    protected static function newFactory(): CatalogoFactory
    {
        return CatalogoFactory::new();
    }

    protected $table = 'catalogos';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoGeneral::class,
        'prefijo' => 'string',
        'orden' => 'integer',
    ];

    /** @return BelongsTo<CatalogoTipo, $this> */
    public function catalogoTipo(): BelongsTo
    {
        return $this->belongsTo(CatalogoTipo::class, 'catalogo_tipo_id');
    }

    /** @return BelongsTo<self, $this> */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    /** @return HasMany<self, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'padre_id');
    }

    /** @return HasMany<Producto, $this> */
    public function productoCategoria(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    /** @return HasMany<Producto, $this> */
    public function productoUnidadMedida(): HasMany
    {
        return $this->hasMany(Producto::class, 'unidad_medida_id');
    }

    /** @return HasMany<Producto, $this> */
    public function productoMarca(): HasMany
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }
}
