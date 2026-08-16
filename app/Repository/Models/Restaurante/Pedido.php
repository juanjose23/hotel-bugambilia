<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $codigo
 * @property int|null $mesa_id
 * @property int|null $mesero_id
 * @property int|null $cliente_id
 * @property int|null $cuenta_id
 * @property EstadoPedido $estado
 * @property float $subtotal
 * @property int $consecutivo_comanda
 * @property int|null $padre_pedido_id
 * @property CarbonInterface|null $abierto_en
 * @property CarbonInterface|null $cerrado_en
 * @property CarbonInterface|null $cargado_en
 * @property string|null $notas
 */
final class Pedido extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'pedidos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPedido::class,
            'subtotal' => 'decimal:2',
            'consecutivo_comanda' => 'integer',
            'abierto_en' => 'datetime',
            'cerrado_en' => 'datetime',
            'cargado_en' => 'datetime',
        ];
    }

    /** @return BelongsTo<Espacio, $this> */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'mesa_id');
    }

    /** @return BelongsTo<Colaborador, $this> */
    public function mesero(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'mesero_id');
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    /** @return BelongsTo<self, $this> */
    public function pedidoPadre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'padre_pedido_id');
    }

    /** @return HasMany<self, $this> */
    public function subCuentas(): HasMany
    {
        return $this->hasMany(self::class, 'padre_pedido_id');
    }

    /** @return HasMany<PedidoItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    /**
     * Calcula el subtotal operativo del pedido sumando los ítems no anulados.
     */
    public function calcularSubtotal(): float
    {
        return (float) $this->items()
            ->whereNot('estado', EstadoItemPedido::ANULADO->value)
            ->sum('subtotal');
    }
}
