<?php

declare(strict_types=1);

namespace App\Repository\Models\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\RecepcionItem;
use App\Repository\Models\Monedas\Moneda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Activo extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'inv_activos';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoActivo::class,
        'fecha_adquisicion' => 'date',
        'fecha_garantia_fin' => 'date',
        'costo_adquisicion' => 'decimal:2',
        'vida_util_meses' => 'integer',
    ];

    /**
     * @return BelongsTo<RegistroIndividualizacion, $this>
     */
    public function individualizacion(): BelongsTo
    {
        return $this->belongsTo(RegistroIndividualizacion::class, 'individualizacion_id');
    }

    /**
     * @return BelongsTo<RecepcionItem, $this>
     */
    public function recepcionItem(): BelongsTo
    {
        return $this->belongsTo(RecepcionItem::class, 'recepcion_item_id');
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

    /**
     * @return BelongsTo<Moneda, $this>
     */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    /**
     * @return BelongsTo<Proveedor, $this>
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * @return HasMany<ActivoAsignacion, $this>
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(ActivoAsignacion::class, 'activo_id');
    }

    /**
     * @return HasOne<ActivoAsignacion, $this>
     */
    public function asignacionActiva(): HasOne
    {
        return $this->hasOne(ActivoAsignacion::class, 'activo_id')
            ->whereNull('fecha_fin')
            ->with('asignable');
    }

    /**
     * @return HasMany<ActivoMantenimiento, $this>
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(ActivoMantenimiento::class, 'activo_id');
    }

    /**
     * @return HasMany<ActivoBaja, $this>
     */
    public function bajas(): HasMany
    {
        return $this->hasMany(ActivoBaja::class, 'activo_id');
    }
}
