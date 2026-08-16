<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Instancia concreta de un cargo aplicado a una cuenta.
 * Conserva fotografía histórica de los valores al momento de aplicación.
 *
 * @property int $id
 * @property int $cuenta_id
 * @property int|null $cargo_id
 * @property TipoCargo $tipo
 * @property string $codigo
 * @property string $nombre
 * @property ModoCargo $modo_calculo
 * @property float $valor
 * @property BaseCalculo $base_calculo
 * @property float $base_monto
 * @property float $monto
 * @property string|null $origen_type
 * @property int|null $origen_id
 * @property int $moneda_id
 * @property int|null $aplicado_por
 * @property int $estado
 * @property string|null $observaciones
 * @property int|null $anulado_por
 * @property Moneda|null $moneda
 */
final class CuentaCargo extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'cuenta_cargos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCargo::class,
            'modo_calculo' => ModoCargo::class,
            'base_calculo' => BaseCalculo::class,
            'valor' => 'decimal:4',
            'base_monto' => 'decimal:2',
            'monto' => 'decimal:2',
            'estado' => 'integer',
        ];
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<Moneda, $this> */
    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    /** @return BelongsTo<CargoFacturacion, $this> */
    public function cargoCatalogo(): BelongsTo
    {
        return $this->belongsTo(CargoFacturacion::class, 'cargo_id');
    }

    /**
     * Origen que motivó la aplicación del cargo.
     *
     * @return MorphTo<Model, $this>
     */
    public function origen(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function aplicadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aplicado_por');
    }

    /** @return BelongsTo<User, $this> */
    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
