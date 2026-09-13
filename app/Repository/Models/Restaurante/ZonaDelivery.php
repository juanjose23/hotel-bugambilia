<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property float $costo_envio
 * @property bool $activo
 * @property list<string> $municipios
 * @property int $orden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class ZonaDelivery extends Model
{
    /** @phpstan-ignore missingType.generics */
    use HasFactory;

    protected $table = 'restaurante_zonas_delivery';

    protected $guarded = ['id'];

    protected $casts = [
        'costo_envio' => 'decimal:2',
        'activo' => 'boolean',
        'municipios' => 'array',
        'orden' => 'integer',
    ];

    /**
     * @param  Builder<ZonaDelivery>  $query
     * @return Builder<ZonaDelivery>
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }

    /**
     * @param  Builder<ZonaDelivery>  $query
     * @return Builder<ZonaDelivery>
     */
    public function scopeOrdenadas(Builder $query): Builder
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
}
