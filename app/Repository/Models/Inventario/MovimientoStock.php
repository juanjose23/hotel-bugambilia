<?php

declare(strict_types=1);

namespace App\Repository\Models\Inventario;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MovimientoStock extends Model implements AuditableContract
{
    use Auditable;

    protected $with = ['lote', 'ubicacionOrigen', 'ubicacionDestino', 'producto', 'tipoCatalogo'];

    protected $table = 'inv_movimientos';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'float',
        'created_at' => 'datetime',
    ];

    /** @return BelongsTo<Lote, $this> */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /** @return BelongsTo<Ubicacion, $this> */
    public function ubicacionOrigen(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_origen_id');
    }

    /** @return BelongsTo<Ubicacion, $this> */
    public function ubicacionDestino(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_destino_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function tipoCatalogo(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'tipo', 'codigo');
    }

    /** @return BelongsTo<User, $this> */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    public function getUsuarioNombreAttribute(): string
    {
        $persona = $this->creadoPor->persona ?? null;
        if (! $persona) {
            return $this->creadoPor->name ?? 'Desconocido';
        }

        return trim(
            ($persona->primer_nombre ?? '')
            .' '.($persona->personaNatural->primer_apellido ?? '')
        ) ?: $this->creadoPor->name ?? 'Desconocido';
    }
}
