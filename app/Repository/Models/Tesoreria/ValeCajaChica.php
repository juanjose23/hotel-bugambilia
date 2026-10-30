<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class ValeCajaChica extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_vales';

    protected $guarded = ['id'];

    protected $casts = ['importe' => 'decimal:2', 'gastado' => 'decimal:2', 'devuelto' => 'decimal:2', 'vence_at' => 'date'];

    /** @return BelongsTo<FondoTesoreria, $this> */
    public function fondo(): BelongsTo
    {
        return $this->belongsTo(FondoTesoreria::class, 'fondo_id');
    }

    /** @return BelongsTo<User, $this> */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }
}
