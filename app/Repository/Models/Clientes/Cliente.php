<?php

declare(strict_types=1);

namespace App\Repository\Models\Clientes;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Personas\Persona;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @method static self create(array<string, mixed> $attributes)
 * @method static self|null find($id)
 */
class Cliente extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'clientes';

    protected $guarded = ['id'];

    protected $casts = [
        'estado' => EstadoGeneral::class,
    ];

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function tipoCliente(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'catalogo_id');
    }
}
