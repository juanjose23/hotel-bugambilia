<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TransformacionMateriaPrimaItem extends Model
{
    protected $table = 'restaurante_transformacion_materia_prima_items';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'costo_asignado' => 'decimal:2',
        'es_merma' => 'boolean',
    ];

    /** @return BelongsTo<TransformacionMateriaPrima, $this> */
    public function transformacion(): BelongsTo
    {
        return $this->belongsTo(TransformacionMateriaPrima::class, 'transformacion_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function productoDestino(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_destino_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function varianteDestino(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_destino_id');
    }

    /** @return BelongsTo<Ubicacion, $this> */
    public function ubicacionDestino(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_destino_id');
    }
}
