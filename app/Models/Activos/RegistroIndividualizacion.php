<?php

declare(strict_types=1);

namespace App\Models\Activos;

use App\Enums\Activos\EstadoIndividualizacion;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Compras\RecepcionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class RegistroIndividualizacion extends Model implements Auditable
{
    /** @use HasFactory<Factory<static>> */
    use AuditableTrait, HasFactory;

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
