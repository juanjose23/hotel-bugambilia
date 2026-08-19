<?php

declare(strict_types=1);

namespace App\Repository\Models\Audits;

use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int|null $usuario_id
 * @property string $tipo_reporte
 * @property array<string, mixed>|null $parametros
 * @property string|null $ruta_archivo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class AuditoriaReporte extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'auditoria_reportes';

    protected $guarded = ['id'];

    protected $casts = [
        'parametros' => 'array',
    ];

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
