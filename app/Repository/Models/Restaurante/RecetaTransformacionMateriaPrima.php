<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class RecetaTransformacionMateriaPrima extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'restaurante_recetas_transformacion_materia_prima';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad_bruta' => 'decimal:4',
        'cantidad_resultado' => 'decimal:4',
        'merma_estimada' => 'decimal:4',
        'estado' => 'boolean',
    ];

    /** @return BelongsTo<Producto, $this> */
    public function productoMateriaPrima(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_materia_prima_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function varianteMateriaPrima(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_materia_prima_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function productoBruto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_bruto_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function varianteBruta(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_bruta_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'unidad_medida_id');
    }
}
