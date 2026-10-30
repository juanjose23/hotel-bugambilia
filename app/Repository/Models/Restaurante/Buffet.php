<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class Buffet extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'restaurante_buffets';

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['fecha_servicio' => 'date', 'precio_persona' => 'decimal:2', 'precio_paquete' => 'decimal:2', 'cerrado_at' => 'datetime', 'cargado_at' => 'datetime', 'comensales_previstos' => 'integer', 'comensales_reales' => 'integer'];
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return HasMany<BuffetPlato, $this> */
    public function menu(): HasMany
    {
        return $this->hasMany(BuffetPlato::class);
    }

    /** @return MorphMany<Stock, $this> */
    public function existencias(): MorphMany
    {
        return $this->morphMany(Stock::class, 'stockable');
    }
}
