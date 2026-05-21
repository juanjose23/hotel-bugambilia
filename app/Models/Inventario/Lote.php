<?php

declare(strict_types=1);

namespace App\Models\Inventario;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\RecepcionItem;
use Database\Factories\Inventario\LoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Lote extends Model implements AuditableContract
{
    /** @use HasFactory<LoteFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $with = ['producto', 'variante', 'ubicacion', 'recepcionItem'];

    protected $table = 'inv_lotes';

    protected $fillable = [
        'codigo_lote',
        'producto_id',
        'producto_variante_id',
        'estado',
        'cantidad_disponible',
        'cantidad_inicial',
        'ubicacion_id',
        'fecha_vencimiento',
        'lote_proveedor',
        'proveedor_id',
        'fecha_recepcion',
        'recepcion_item_id',
    ];

    protected $casts = [
        'estado' => EstadoLote::class,
        'cantidad_disponible' => 'float',
        'cantidad_inicial' => 'float',
        'fecha_vencimiento' => 'date:Y-m-d',
        'fecha_recepcion' => 'date:Y-m-d',
    ];

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /**
     * @return BelongsTo<ProductoVariante, $this>
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    /**
     * @return HasMany<Lote, $this>
     */
    public function lotesAsociados(): HasMany
    {
        return $this->hasMany(Lote::class, 'lote_proveedor', 'lote_proveedor')
            ->whereNotNull('lote_proveedor')
            ->where('id', '!=', $this->id);
    }

    /**
     * @return HasMany<MovimientoStock, $this>
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoStock::class, 'lote_id')->orderBy('created_at', 'desc');
    }

    /**
     * @return BelongsTo<RecepcionItem, $this>
     */
    public function recepcionItem(): BelongsTo
    {
        return $this->belongsTo(RecepcionItem::class, 'recepcion_item_id');
    }

    /**
     * @return Collection<int, Lote>
     */
    public function getSiblingLotesAttribute(): Collection
    {
        $query = self::where('id', '!=', $this->id);

        if ($this->lote_proveedor) {
            $query->where('lote_proveedor', $this->lote_proveedor);
        } elseif ($this->recepcion_item_id) {
            $recepcionId = $this->recepcionItem?->recepcion_id;
            if ($recepcionId) {
                $query->whereHas('recepcionItem', function ($q) use ($recepcionId) {
                    $q->where('recepcion_id', $recepcionId);
                });
            } else {
                return collect();
            }
        } else {
            return collect();
        }

        return $query->get();
    }
}
