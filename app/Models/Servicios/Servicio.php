<?php

declare(strict_types=1);

namespace App\Models\Servicios;

use App\Models\Catalogos\Catalogo;
use App\Models\Shared\Imagen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Servicio extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $guarded = ['id'];

    /** @var array<int, string> */
    protected array $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'estado' => 'integer',
    ];

    protected $table = 'servicios';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria_id',
        'descripcion',
        'icono',
        'estado',
    ];

    /** @return BelongsTo<Catalogo, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'categoria_id');
    }

    /** @return HasMany<ServiciosPrecio, $this> */
    public function precios(): HasMany
    {
        return $this->hasMany(ServiciosPrecio::class, 'servicio_id');
    }

    /** @return MorphMany<Imagen, $this> */
    public function imagenes(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'imagenable');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 1);
    }
}
