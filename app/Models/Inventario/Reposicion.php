<?php

declare(strict_types=1);

namespace App\Models\Inventario;

use App\Models\Catalogos\Ubicacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Reposicion extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'inv_reposiciones';

    protected $fillable = [
        'codigo',
        'origen_id',
        'destino_id',
        'estado',
        'creado_por_id',
        'procesado_por_id',
        'fecha_proceso',
        'notas',
    ];

    protected $casts = [
        'fecha_proceso' => 'datetime',
    ];

    /**
     * @return HasMany<ReposicionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReposicionItem::class, 'reposicion_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function origen(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'origen_id');
    }

    /**
     * @return BelongsTo<Ubicacion, $this>
     */
    public function destino(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'destino_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por_id');
    }
}
