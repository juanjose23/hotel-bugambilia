<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class TransformacionMateriaPrima extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'restaurante_transformaciones_materia_prima';

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad_procesada' => 'decimal:4',
        'costo_total' => 'decimal:2',
    ];

    /** @return BelongsTo<Producto, $this> */
    public function productoOrigen(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_origen_id');
    }

    /** @return BelongsTo<ProductoVariante, $this> */
    public function varianteOrigen(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_origen_id');
    }

    /** @return BelongsTo<Ubicacion, $this> */
    public function ubicacionOrigen(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_origen_id');
    }

    /** @return BelongsTo<User, $this> */
    public function realizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }

    /** @return HasMany<TransformacionMateriaPrimaItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(TransformacionMateriaPrimaItem::class, 'transformacion_id');
    }
}
