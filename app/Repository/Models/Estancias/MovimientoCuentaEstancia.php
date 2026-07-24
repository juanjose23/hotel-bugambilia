<?php

declare(strict_types=1);

namespace App\Repository\Models\Estancias;

use App\Enums\Estancias\TipoMovimientoCuenta;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class MovimientoCuentaEstancia extends Model
{
    protected $table = 'movimientos_cuenta_estancia';

    protected $fillable = [
        'cuenta_estancia_id', 'tipo', 'concepto', 'monto',
        'origen_type', 'origen_id', 'usuario_id', 'metadatos',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimientoCuenta::class,
            'monto' => 'decimal:2',
            'metadatos' => 'array',
        ];
    }

    /** @return BelongsTo<CuentaEstancia, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(CuentaEstancia::class, 'cuenta_estancia_id');
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function origen(): MorphTo
    {
        return $this->morphTo();
    }
}
