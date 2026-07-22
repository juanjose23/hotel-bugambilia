<?php

declare(strict_types=1);

namespace App\Repository\Models\Compras;

use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class CompraHistorial extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'compra_historial';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'model_id' => 'integer',
            'usuario_id' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
