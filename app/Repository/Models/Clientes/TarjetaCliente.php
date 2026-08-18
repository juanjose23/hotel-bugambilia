<?php

declare(strict_types=1);

namespace App\Repository\Models\Clientes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $cliente_id
 * @property string $stripe_payment_method_id
 * @property string $stripe_customer_id
 * @property string $ultimo_digitos
 * @property string $marca
 * @property int $exp_month
 * @property int $exp_year
 * @property bool $es_predeterminada
 */
final class TarjetaCliente extends Model
{
    use SoftDeletes;

    protected $table = 'tarjeta_cliente';

    protected $guarded = ['id'];

    protected $casts = [
        'exp_month' => 'integer',
        'exp_year' => 'integer',
        'es_predeterminada' => 'boolean',
    ];

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
