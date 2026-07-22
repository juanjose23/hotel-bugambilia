<?php

declare(strict_types=1);

namespace App\Repository\Models\Activos;

use App\Enums\Activos\EstadoIndividualizacion;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Compras\RecepcionItem;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class RegistroIndividualizacion extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'inv_registro_individualizacion';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoIndividualizacion::class,
        'fecha_completado' => 'datetime',
        'cantidad_total' => 'integer',
        'cantidad_registrada' => 'integer',
    ];

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
     * @return BelongsTo<User, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    /**
     * @return HasMany<Activo, $this>
     */
    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'individualizacion_id');
    }
}
