<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Renglón de cargo/débito dentro de una Cuenta.
 *
 * El polimorfismo `origen` apunta al modelo que generó el cargo:
 *   - PedidoItem::class  → consumo de restaurante
 *   - Estancia::class    → cargo de noche de hospedaje
 *   - Servicio::class    → spa, lavandería, transporte, etc.
 *
 * @property int $id
 * @property int $cuenta_id
 * @property string|null $origen_type
 * @property int|null $origen_id
 * @property int|null $espacio_id
 * @property string $concepto
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property float $descuento
 * @property float $impuesto
 * @property float $total
 * @property array<string, mixed>|null $metadatos
 * @property int|null $creador_id
 */
final class CuentaDetalle extends Model
{
    protected $table = 'cuenta_detalles';

    protected $fillable = [
        'cuenta_id',
        'origen_type', 'origen_id',
        'espacio_id',
        'concepto', 'cantidad', 'precio_unitario',
        'subtotal', 'descuento', 'impuesto', 'total',
        'metadatos', 'creador_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'total' => 'decimal:2',
            'metadatos' => 'array',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /**
     * Modelo que originó el cargo: PedidoItem, Estancia, Servicio, etc.
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
}
