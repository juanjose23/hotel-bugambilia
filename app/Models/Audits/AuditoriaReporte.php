<?php

namespace App\Models\Audits;

use App\Models\User;
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
 * @property int $conteo_descargas
 * @property Carbon|null $ultima_descarga_en
 */
class AuditoriaReporte extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'auditoria_reportes';

    protected $guarded = ['id'];

    protected $casts = [
        'parametros' => 'array',
        'ultima_descarga_en' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
