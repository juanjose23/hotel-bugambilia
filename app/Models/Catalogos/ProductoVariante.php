<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int $producto_id
 * @property string $codigo
 * @property string $nombre_variante
 * @property string|null $barcode_base64
 * @property array<string, mixed>|null $atributos
 * @property int|null $unidad_medida_id
 * @property float|null $peso
 * @property float|null $volumen
 * @property int $estado
 */
class ProductoVariante extends Model implements AuditableContract
{
    //
    use Auditable, SoftDeletes;

    protected $table = 'producto_variantes';

    protected $fillable = ['producto_id'];

    protected $casts = [
        'atributos' => 'array',
        'peso' => 'decimal:2',
        'volumen' => 'decimal:2',
    ];

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'unidad_medida_id');
    }
}
