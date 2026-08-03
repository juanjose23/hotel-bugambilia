<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Renglón de consumo puro dentro de una Cuenta.
 * Los impuestos, descuentos y otros cargos se gestionan en cuenta_cargos.
 *
 * El polimorfismo `origen` apunta al modelo que generó el consumo:
 *   - PedidoItem::class  → consumo de restaurante
 *   - Estancia::class    → cargo de noche de hospedaje
 *   - Servicio::class    → spa, lavandería, transporte, etc.
 *
 * @property int $id
 * @property int $cuenta_id
 * @property string|null $origen_type
 * @property int|null $origen_id
 * @property int|null $tipo_detalle
 * @property int|null $espacio_id
 * @property string $concepto
 * @property string|null $descripcion
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property float $total
 * @property int $estado
 * @property array<string, mixed>|null $metadatos
 * @property int $moneda_id
 * @property int|null $creador_id
 * @property int|null $anulado_por
 * @property string|null $anulado_en
 * @property Moneda|null $moneda
 */
final class CuentaDetalle extends Model
{
    protected $table = 'cuenta_detalles';

    protected $fillable = [
        'cuenta_id',
        'moneda_id',
        'origen_type', 'origen_id',
        'tipo_detalle',
        'espacio_id',
        'concepto', 'descripcion',
        'cantidad', 'precio_unitario',
        'subtotal', 'total',
        'estado',
        'metadatos', 'creador_id',
        'anulado_por', 'anulado_en',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'estado' => 'integer',
            'metadatos' => 'array',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────

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

    /**
     * Modelo que originó el consumo: PedidoItem, Estancia, Servicio, etc.
     *
     * @return MorphTo<Model, $this>
     */
    public function origen(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Espacio, $this> */
    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /** @return BelongsTo<User, $this> */
    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
