<?php

declare(strict_types=1);

namespace App\Repository\Models\Estancias;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\TipoTitular;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $estancia_id
 * @property int|null $reserva_id
 * @property string|null $cuentaable_type
 * @property int|null $cuentaable_id
 * @property TipoTitular $tipo_titular
 * @property string|null $numero_folio
 * @property string $numero_cuenta
 * @property EstadoCuentaEstancia $estado
 * @property float|null $limite_autorizado
 * @property float|null $monto_limite
 * @property float $total_cargos
 * @property float $total_pagos
 * @property float $saldo
 * @property Carbon|null $abierta_at
 * @property Carbon|null $cerrada_at
 * @property int|null $abierta_por
 * @property int|null $cerrada_por
 * @property Estancia|null $estancia
 * @property Reserva|null $reserva
 */
final class CuentaEstancia extends Model
{
    protected $table = 'cuentas_estancia';

    protected $fillable = [
        'estancia_id', 'reserva_id', 'cuentaable_type', 'cuentaable_id',
        'tipo_titular', 'numero_folio', 'numero_cuenta', 'estado',
        'limite_autorizado', 'monto_limite', 'total_cargos', 'total_pagos',
        'saldo', 'abierta_at', 'cerrada_at', 'abierta_por', 'cerrada_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo_titular' => TipoTitular::class,
            'estado' => EstadoCuentaEstancia::class,
            'limite_autorizado' => 'decimal:2',
            'monto_limite' => 'decimal:2',
            'total_cargos' => 'decimal:2',
            'total_pagos' => 'decimal:2',
            'saldo' => 'decimal:2',
            'abierta_at' => 'datetime',
            'cerrada_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Estancia, $this> */
    public function estancia(): BelongsTo
    {
        return $this->belongsTo(Estancia::class);
    }

    /** @return BelongsTo<Reserva, $this> */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /** @return MorphTo<Model, $this> */
    public function cuentaable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function usuarioQueAbrio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abierta_por');
    }

    /** @return BelongsTo<User, $this> */
    public function usuarioQueCerro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    /** @return HasMany<MovimientoCuentaEstancia, $this> */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCuentaEstancia::class);
    }

    public function estaAbierta(): bool
    {
        return $this->estado === EstadoCuentaEstancia::ABIERTA;
    }

    public function puedeRegistrarMovimiento(): bool
    {
        return $this->estaAbierta();
    }
}
